<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241119141313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814ABC54C8C93');
        $this->addSql('DROP INDEX IDX_D11814ABC54C8C93 ON intervention');
        $this->addSql('ALTER TABLE intervention ADD type VARCHAR(255) NOT NULL, DROP type_id, CHANGE date_start date_start DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE date_end date_end DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A090B42E');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398C3568B40');
        $this->addSql('DROP INDEX IDX_F5299398A090B42E ON `order`');
        $this->addSql('DROP INDEX IDX_F5299398C3568B40 ON `order`');
        $this->addSql('ALTER TABLE `order` ADD offer VARCHAR(255) NOT NULL, ADD customer VARCHAR(255) NOT NULL, DROP offers_id, DROP customers_id');
        $this->addSql('ALTER TABLE unit DROP FOREIGN KEY FK_DCBB0C535D83CC1');
        $this->addSql('ALTER TABLE unit DROP FOREIGN KEY FK_DCBB0C53C54C8C93');
        $this->addSql('ALTER TABLE unit DROP FOREIGN KEY FK_DCBB0C53DF9BA23B');
        $this->addSql('DROP INDEX IDX_DCBB0C53C54C8C93 ON unit');
        $this->addSql('DROP INDEX IDX_DCBB0C535D83CC1 ON unit');
        $this->addSql('DROP INDEX IDX_DCBB0C53DF9BA23B ON unit');
        $this->addSql('ALTER TABLE unit ADD type VARCHAR(255) NOT NULL, ADD state VARCHAR(255) NOT NULL, ADD bay VARCHAR(255) NOT NULL, DROP type_id, DROP state_id, DROP bay_id');
        $this->addSql('ALTER TABLE user CHANGE adress address VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE intervention ADD type_id INT DEFAULT NULL, DROP type, CHANGE date_start date_start DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE date_end date_end DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814ABC54C8C93 FOREIGN KEY (type_id) REFERENCES type_intervention (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_D11814ABC54C8C93 ON intervention (type_id)');
        $this->addSql('ALTER TABLE `order` ADD offers_id INT DEFAULT NULL, ADD customers_id INT DEFAULT NULL, DROP offer, DROP customer');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A090B42E FOREIGN KEY (offers_id) REFERENCES offer (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398C3568B40 FOREIGN KEY (customers_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_F5299398A090B42E ON `order` (offers_id)');
        $this->addSql('CREATE INDEX IDX_F5299398C3568B40 ON `order` (customers_id)');
        $this->addSql('ALTER TABLE unit ADD type_id INT DEFAULT NULL, ADD state_id INT DEFAULT NULL, ADD bay_id INT DEFAULT NULL, DROP type, DROP state, DROP bay');
        $this->addSql('ALTER TABLE unit ADD CONSTRAINT FK_DCBB0C535D83CC1 FOREIGN KEY (state_id) REFERENCES state_unit (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE unit ADD CONSTRAINT FK_DCBB0C53C54C8C93 FOREIGN KEY (type_id) REFERENCES type_unit (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE unit ADD CONSTRAINT FK_DCBB0C53DF9BA23B FOREIGN KEY (bay_id) REFERENCES bay (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_DCBB0C53C54C8C93 ON unit (type_id)');
        $this->addSql('CREATE INDEX IDX_DCBB0C535D83CC1 ON unit (state_id)');
        $this->addSql('CREATE INDEX IDX_DCBB0C53DF9BA23B ON unit (bay_id)');
        $this->addSql('ALTER TABLE `user` CHANGE address adress VARCHAR(255) DEFAULT NULL');
    }
}
