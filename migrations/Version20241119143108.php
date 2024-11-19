<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241119143108 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` ADD offer_id INT NOT NULL, ADD customer_id INT NOT NULL, DROP offer, DROP customer');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939853C674EE FOREIGN KEY (offer_id) REFERENCES offer (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993989395C3F3 FOREIGN KEY (customer_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_F529939853C674EE ON `order` (offer_id)');
        $this->addSql('CREATE INDEX IDX_F52993989395C3F3 ON `order` (customer_id)');
        $this->addSql('ALTER TABLE unit ADD type_id INT NOT NULL, ADD state_id INT NOT NULL, ADD bay_id INT NOT NULL, DROP type, DROP state, DROP bay');
        $this->addSql('ALTER TABLE unit ADD CONSTRAINT FK_DCBB0C53C54C8C93 FOREIGN KEY (type_id) REFERENCES type_unit (id)');
        $this->addSql('ALTER TABLE unit ADD CONSTRAINT FK_DCBB0C535D83CC1 FOREIGN KEY (state_id) REFERENCES state_unit (id)');
        $this->addSql('ALTER TABLE unit ADD CONSTRAINT FK_DCBB0C53DF9BA23B FOREIGN KEY (bay_id) REFERENCES bay (id)');
        $this->addSql('CREATE INDEX IDX_DCBB0C53C54C8C93 ON unit (type_id)');
        $this->addSql('CREATE INDEX IDX_DCBB0C535D83CC1 ON unit (state_id)');
        $this->addSql('CREATE INDEX IDX_DCBB0C53DF9BA23B ON unit (bay_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939853C674EE');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993989395C3F3');
        $this->addSql('DROP INDEX IDX_F529939853C674EE ON `order`');
        $this->addSql('DROP INDEX IDX_F52993989395C3F3 ON `order`');
        $this->addSql('ALTER TABLE `order` ADD offer VARCHAR(255) NOT NULL, ADD customer VARCHAR(255) NOT NULL, DROP offer_id, DROP customer_id');
        $this->addSql('ALTER TABLE unit DROP FOREIGN KEY FK_DCBB0C53C54C8C93');
        $this->addSql('ALTER TABLE unit DROP FOREIGN KEY FK_DCBB0C535D83CC1');
        $this->addSql('ALTER TABLE unit DROP FOREIGN KEY FK_DCBB0C53DF9BA23B');
        $this->addSql('DROP INDEX IDX_DCBB0C53C54C8C93 ON unit');
        $this->addSql('DROP INDEX IDX_DCBB0C535D83CC1 ON unit');
        $this->addSql('DROP INDEX IDX_DCBB0C53DF9BA23B ON unit');
        $this->addSql('ALTER TABLE unit ADD type VARCHAR(255) NOT NULL, ADD state VARCHAR(255) NOT NULL, ADD bay VARCHAR(255) NOT NULL, DROP type_id, DROP state_id, DROP bay_id');
    }
}
