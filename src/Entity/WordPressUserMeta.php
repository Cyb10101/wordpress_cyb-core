<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WordPressUserMetaRepository")
 * @ORM\Table(name="wp_usermeta", indexes={@ORM\Index(name="user_id", columns={"user_id"}), @ORM\Index(name="meta_key", columns={"meta_key"})})
 */
class WordPressUserMeta {
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="bigint", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $umeta_id = 0;

    /**
     * @var int
     * @ORM\Column(type="bigint", options={"unsigned": true, "default": 0})
     */
    private $user_id = 0;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false, options={"collation":"utfutf8mb4_unicode_520_ci8_bin"})
     */
    private $meta_key = '';

    /**
     * @var string
     * @ORM\Column(type="string", length=191, nullable=false, options={"collation":"utf8mb4_unicode_520_ci"})
     */
    private $meta_value = '';

    /**
     * @return int
     */
    public function getUmetaId(): int {
        return $this->umeta_id;
    }

    /**
     * @param int $umeta_id
     * @return self
     */
    public function setUmetaId($umeta_id) {
        $this->umeta_id = $umeta_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     * @return self
     */
    public function setUserId($user_id) {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getMetaKey(): string {
        return $this->meta_key;
    }

    /**
     * @param string $meta_key
     * @return self
     */
    public function setMetaKey($meta_key) {
        $this->meta_key = $meta_key;
        return $this;
    }

    /**
     * @return string
     */
    public function getMetaValue(): string {
        return $this->meta_value;
    }

    /**
     * @param string $meta_value
     * @return self
     */
    public function setMetaValue($meta_value) {
        $this->meta_value = $meta_value;
        return $this;
    }
}
