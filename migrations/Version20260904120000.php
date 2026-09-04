<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260904120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add profile and product image paths used by the application';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD profile_image_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE cables ADD image_path VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP profile_image_path');
        $this->addSql('ALTER TABLE cables DROP image_path');
    }
}
