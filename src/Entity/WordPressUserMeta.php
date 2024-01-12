<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\WordPressUserMetaRepository::class)]
#[ORM\Table(name: 'wp_usermeta')]
#[ORM\Index(name: 'user_id', columns: ['user_id'])]
#[ORM\Index(name: 'meta_key', columns: ['meta_key'])]
class WordPressUserMeta {
    #[ORM\Id]
    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $umeta_id = null;

    #[ORM\Column(type: 'bigint', options: ['unsigned' => true, 'default' => 0])]
    private int $user_id = 0;

    #[ORM\Column(type: 'string', length: 255, nullable: false, options: ['collation' => 'utfutf8mb4_unicode_520_ci8_bin'])]
    private string $meta_key = '';

    #[ORM\Column(type: 'string', length: 191, nullable: false, options: ['collation' => 'utf8mb4_unicode_520_ci'])]
    private string $meta_value = '';

    public function getUmetaId(): int {
        return $this->umeta_id;
    }

    public function setUmetaId(int $umeta_id): self {
        $this->umeta_id = $umeta_id;
        return $this;
    }

    public function getUserId(): int {
        return $this->user_id;
    }

    public function setUserId(int $user_id): self {
        $this->user_id = $user_id;
        return $this;
    }

    public function getMetaKey(): string {
        return $this->meta_key;
    }

    public function setMetaKey(string $meta_key): self {
        $this->meta_key = $meta_key;
        return $this;
    }

    public function getMetaValue(): string {
        return $this->meta_value;
    }

    public function setMetaValue(string $meta_value): self {
        $this->meta_value = $meta_value;
        return $this;
    }
}
