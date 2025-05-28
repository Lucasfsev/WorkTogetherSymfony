<?php

namespace App\Command;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Repository\TypeUnitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:renew-orders',
    description: 'Vérifie et renouvelle automatiquement les commandes qui expirent aujourd\'hui',
)]
class RenewCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private OrderRepository $orderRepository;
    private TypeUnitRepository $typeUnitRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        OrderRepository $orderRepository,
        TypeUnitRepository $typeUnitRepository
    ) {
        $this->entityManager = $entityManager;
        $this->orderRepository = $orderRepository;
        $this->typeUnitRepository = $typeUnitRepository;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Affiche les commandes qui seraient renouvelées/supprimées sans les modifier')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force le renouvellement même pour les commandes déjà expirées (dans les 2 jours)')
            ->addOption('no-delete', null, InputOption::VALUE_NONE, 'Désactive la suppression automatique des commandes expirées depuis plus de 2 jours')
            ->setHelp('Cette commande vérifie toutes les commandes, renouvelle celles qui expirent aujourd\'hui et supprime celles expirées depuis plus de 2 jours.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $isDryRun = $input->getOption('dry-run');
        $force = $input->getOption('force');
        $noDelete = $input->getOption('no-delete');

        $io->title('Vérification, renouvellement et nettoyage des commandes');

        try {
            $today = new \DateTime();
            $today->setTime(0, 0, 0); // Début de journée pour comparaison précise

            $twoDaysAgo = clone $today;
            $twoDaysAgo->sub(new \DateInterval('P2D')); // Il y a 2 jours

            // Récupérer toutes les commandes qui ont une date de fin
            $orders = $this->orderRepository->findOrdersWithEndDate();

            if (empty($orders)) {
                $io->info('Aucune commande avec date de fin trouvée.');
                return Command::SUCCESS;
            }

            $renewedCount = 0;
            $expiredCount = 0;
            $expiresTodayCount = 0;
            $deletedCount = 0;

            $io->progressStart(count($orders));

            foreach ($orders as $order) {
                $endDate = $order->getEndDate();

                if (!$endDate) {
                    $io->progressAdvance();
                    continue;
                }

                // Normaliser la date de fin pour comparaison
                $orderEndDate = clone $endDate;
                $orderEndDate->setTime(0, 0, 0);

                // Vérifier si la commande est expirée depuis plus de 2 jours -> SUPPRESSION
                if (!$noDelete && $orderEndDate < $twoDaysAgo) {
                    $this->deleteExpiredOrder($order, $io, $isDryRun);
                    $deletedCount++;
                }
                // Vérifier si la commande est expirée mais dans les 2 jours
                elseif ($orderEndDate < $today) {
                    $expiredCount++;

                    if ($force) {
                        $this->renewOrder($order, $io, $isDryRun);
                        $renewedCount++;
                    } else {
                        $daysSinceExpiry = $today->diff($orderEndDate)->days;
                        $io->warning(sprintf(
                            'Commande #%s expirée depuis %d jour(s) (%s) - Sera supprimée dans %d jour(s) (utilisez --force pour la renouveler)',
                            $order->getId(),
                            $daysSinceExpiry,
                            $endDate->format('d/m/Y'),
                            2 - $daysSinceExpiry
                        ));
                    }
                }
                // Vérifier si la commande expire aujourd'hui
                elseif ($orderEndDate == $today) {
                    $expiresTodayCount++;
                    $this->renewOrder($order, $io, $isDryRun);
                    $renewedCount++;
                }

                $io->progressAdvance();
            }

            $io->progressFinish();

            // Sauvegarder les modifications si ce n'est pas un dry-run
            if (!$isDryRun && ($renewedCount > 0 || $deletedCount > 0)) {
                $this->entityManager->flush();
                $io->success(sprintf('Base de données mise à jour : %d renouvellements, %d suppressions.', $renewedCount, $deletedCount));
            }

            // Afficher le résumé
            $io->section('Résumé');
            $io->table(
                ['Statut', 'Nombre'],
                [
                    ['Commandes vérifiées', count($orders)],
                    ['Expirent aujourd\'hui', $expiresTodayCount],
                    ['Expirées (dans les 2 jours)', $expiredCount],
                    ['Supprimées (>2 jours)', $deletedCount],
                    ['Renouvelées', $renewedCount],
                ]
            );

            if ($isDryRun) {
                $io->note('Mode dry-run activé : aucune modification n\'a été effectuée.');
            }

            if ($deletedCount > 0 && !$isDryRun) {
                $io->warning('Des commandes ont été supprimées définitivement.');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error(sprintf('Erreur lors de l\'exécution : %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function deleteExpiredOrder(Order $order, SymfonyStyle $io, bool $isDryRun): void
    {
        $endDate = $order->getEndDate();
        $today = new \DateTime();
        $daysSinceExpiry = $today->diff($endDate)->days;

        if ($isDryRun) {
            $io->text(sprintf(
                '[DRY-RUN] Commande #%s SUPPRIMÉE : expirée le %s (il y a %d jours)',
                $order->getId(),
                $endDate->format('d/m/Y'),
                $daysSinceExpiry
            ));
        } else {
            // Libérer les unités associées (comme dans votre contrôleur)
            $units = $order->getUnits();
            $freeType = $this->typeUnitRepository->findOneBy(['reference' => 'Libre']);

            if ($freeType) {
                foreach ($units as $unit) {
                    $unit->setType($freeType);
                    $unit->removeOrder($order);
                }
            }

            // Retirer la commande du customer
            $customer = $order->getCustomer();
            if ($customer) {
                $customer->removeOrder($order);
            }

            // Supprimer la commande
            $this->entityManager->remove($order);

            $io->text(sprintf(
                'Commande #%s SUPPRIMÉE : expirée le %s (il y a %d jours) - %d unités libérées',
                $order->getId(),
                $endDate->format('d/m/Y'),
                $daysSinceExpiry,
                count($units)
            ));
        }
    }

    private function renewOrder(Order $order, SymfonyStyle $io, bool $isDryRun): void
    {
        $oldEndDate = $order->getEndDate();

        // Calculer la nouvelle date de fin (ajouter un mois)
        $newEndDate = clone $oldEndDate;
        $newEndDate->add(new \DateInterval('P1M')); // Ajouter 1 mois

        if ($isDryRun) {
            $io->text(sprintf(
                '[DRY-RUN] Commande #%s : %s → %s (+1 mois)',
                $order->getId(),
                $oldEndDate->format('d/m/Y'),
                $newEndDate->format('d/m/Y')
            ));
        } else {
            $order->setEndDate($newEndDate);

            $io->text(sprintf(
                'Commande #%s renouvelée : %s → %s (+1 mois)',
                $order->getId(),
                $oldEndDate->format('d/m/Y'),
                $newEndDate->format('d/m/Y')
            ));

            // Optionnel : ajouter un log ou une notification
            // $this->logRenewal($order, $oldEndDate, $newEndDate);
        }
    }

    /**
     * Méthode optionnelle pour logger les renouvellements
     */
    private function logRenewal(Order $order, \DateTime $oldDate, \DateTime $newDate): void
    {
        // Vous pouvez implémenter un système de log ici
        // Par exemple, créer une entité RenewalLog ou utiliser le système de log Symfony
    }
}