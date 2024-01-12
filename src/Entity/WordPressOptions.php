<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\WordPressOptionsRepository::class)]
#[ORM\Table(name: 'wp_options')]
#[ORM\UniqueConstraint(name: 'option_name', columns: ['option_name'])]
class WordPressOptions {
    #[ORM\Id]
    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $option_id = null;

    #[ORM\Column(type: 'string', length: 191, nullable: false, options: ['default' => '', 'collation' => 'utf8mb4_unicode_520_ci'])]
    private string $option_name = '';

    #[ORM\Column(type: 'text', nullable: false, options: ['collation' => 'utfutf8mb4_unicode_520_ci8_bin'])]
    private string $option_value = '';

    #[ORM\Column(type: 'string', length: 20, nullable: false, options: ['default' => 'yes', 'collation' => 'utf8mb4_unicode_520_ci'])]
    private string $autoload = '';

    public function getOptionId(): int {
        return $this->option_id;
    }

    public function setOptionId(int $option_id): self {
        $this->option_id = $option_id;
        return $this;
    }

    public function getOptionName(): string {
        return $this->option_name;
    }

    public function setOptionName(string $option_name): self {
        $this->option_name = $option_name;
        return $this;
    }

    public function getOptionValue(): string {
        return $this->option_value;
    }

    public function setOptionValue(string $option_value): self {
        $this->option_value = $option_value;
        return $this;
    }

    public function getAutoload(): string {
        return $this->autoload;
    }

    public function setAutoload(string $autoload): self {
        $this->autoload = $autoload;
        return $this;
    }
}
