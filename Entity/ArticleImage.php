<?php

namespace NewsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ArticleImage
 *
 * @ORM\Table(name="news_article_image", uniqueConstraints={@ORM\UniqueConstraint(name="inx_uuid", columns={"uuid"})}, indexes={@ORM\Index(name="inx_file_name", columns={"file_name"}), @ORM\Index(name="inx_news_id", columns={"news_id"}), @ORM\Index(name="inx_file_extension", columns={"file_extension"}), @ORM\Index(name="inx_file_size", columns={"file_size"}), @ORM\Index(name="inx_created_at", columns={"created_at"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class ArticleImage
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="uuid", type="string", length=50, nullable=true)
     */
    private $uuid;

    /**
     * @var string
     *
     * @ORM\Column(name="file_name", type="string", length=255, nullable=true)
     */
    private $fileName;

    /**
     * @var string
     *
     * @ORM\Column(name="file_extension", type="string", length=255, nullable=true)
     */
    private $fileExtension;

    /**
     * @var integer
     *
     * @ORM\Column(name="file_size", type="integer", nullable=false)
     */
    private $fileSize;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var Article
     *
     * @ORM\ManyToOne(targetEntity="NewsBundle\Entity\Article", inversedBy="images")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="news_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $article;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Set fileName
     *
     * @param string $fileName
     *
     * @return ArticleImage
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get fileName
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set fileExtension
     *
     * @param string $fileExtension
     *
     * @return ArticleImage
     */
    public function setFileExtension($fileExtension)
    {
        $this->fileExtension = $fileExtension;

        return $this;
    }

    /**
     * Get fileExtension
     *
     * @return string
     */
    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    /**
     * Set fileSize
     *
     * @param integer $fileSize
     *
     * @return ArticleImage
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    /**
     * Get fileSize
     *
     * @return integer
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * Set Article
     *
     * @param Article $article
     *
     * @return ArticleImage
     */
    public function setArticle(Article $article = null)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * Get Article
     *
     * @return Article
     */
    public function getArticle()
    {
        return $this->article;
    }


    public function getImageUploadDir()
    {
        return 'upload/news';
    }

    public function getImageUploadRootDir()
    {
        return __DIR__.'/../../../web/'.$this->getImageUploadDir();
    }

    public function deleteCurrentImageFile()
    {
        $fileName = $this->getFileName();
        if ($fileName) {
            $this->setFileName(null);
            $fullPath = $this->getImageUploadRootDir().'/'.$fileName;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PostRemove()
     */
    public function afterRemove()
    {
        $this->deleteCurrentImageFile();
    }

    /**
     * @ORM\PrePersist
     */
    public function beforeInsert()
    {
        $now = new \DateTime();
        $this->setCreatedAt($now);
    }

    public function __toString()
    {
        return ''.$this->getFileName();
    }
}
