<?php

namespace NewsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FileBundle\Service\FileService\FileService;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Article
 *
 * @ORM\Table(name="news_article", uniqueConstraints={@ORM\UniqueConstraint(name="inx_uuid", columns={"uuid"})}, indexes={@ORM\Index(name="inx_title", columns={"title"}), @ORM\Index(name="inx_author", columns={"author"}), @ORM\Index(name="inx_position", columns={"position"}), @ORM\Index(name="inx_created_at", columns={"created_at"}), @ORM\Index(name="inx_updated_at", columns={"updated_at"}), @ORM\Index(name="inx_active", columns={"active"})})
 * @ORM\Entity(repositoryClass="NewsBundle\Entity\Repository\Article\Repository")
 * @ORM\HasLifecycleCallbacks()
 * @Serializer\ExclusionPolicy("all")
 */
class Article
{

    /** @var  FileService $fileService */
    private $fileService;

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
     * @var integer
     *
     * @ORM\Column(name="language_id", type="integer", nullable=false)
     */
    private $language;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="author", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    private $position;


    /**
     * @var string
     *
     * @ORM\Column(name="photo", type="string", length=255, nullable=true)
     */
    private $photo;
    private $tempPhoto;

    /**
     * @Assert\File(
     *  maxSize="3M",
     *  mimeTypes = {"image/jpg", "image/jpeg", "image/png"}
     * )
     */
    private $photoFile;


    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = true;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     */
    private $updatedAt;


    /**
     * @ORM\OneToMany(targetEntity="NewsBundle\Entity\ArticleImage", mappedBy="article", cascade={"all"})
     */
    private $images;


    public function __construct()
    {
        $this->images = new ArrayCollection();
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * Set uuid
     *
     * @param string $uuid
     *
     * @return Article
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Get uuid
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return integer
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set language
     *
     * @param integer $language
     *
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }


    /**
     * Set photo
     *
     * @param string $image
     *
     * @return $this
     */
    public function setPhoto($image)
    {
        $this->photo = $image;
        return $this;
    }

    /**
     * Get photo
     *
     * @return string
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhotoFile(UploadedFile $photoFile = null)
    {
        $this->photoFile = $photoFile;
        // check if we have an old image path
        if (isset($this->photo)) {
            // store the old name to delete after the update
            $this->tempPhoto = $this->photo;
            $this->photo = null;
        } else {
            $this->photo = 'initial';
        }
    }

    public function getPhotoFile()
    {
        return $this->photoFile;
    }


    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }


    /**
     * @return FileService
     */
    public function getFileService()
    {
        return $this->fileService;
    }

    /**
     * @param mixed $fileService
     * @return $this
     */
    public function setFileService($fileService)
    {
        $this->fileService = $fileService;
        return $this;
    }


    /**
     * @ORM\PrePersist
     */
    public function beforeInsert()
    {
        $now = new \DateTime();
        $this->setCreatedAt($now);
        $this->setUpdatedAt($now);
    }

    /**
     * @ORM\PreUpdate
     */
    public function beforeUpdate()
    {
        $this->setUpdatedAt(new \DateTime());
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        $serviceBaseFolder = 'news/article';
        if (null !== $this->getPhotoFile()) {
            $info = $this->getFileService()->uploadFile($this->getPhotoFile(), "{$serviceBaseFolder}/photo");
            $this->photoFile = null;
            $this->setPhoto($info['relativeFullFilePath']);
        }
        if (isset($this->tempPhoto)) {
            $this->getFileService()->removeFile($this->tempPhoto);
            $this->tempPhoto = null;
        }
    }


    /**
     * @ORM\PostRemove()
     */
    public function removeFiles()
    {
        $this->deleteCurrentPhoto();
    }

    public function deleteCurrentPhoto()
    {
        $this->getFileService()->removeFile($this->getPhoto());
        $this->setPhoto(null);
    }

    public function getImages()
    {
        return $this->images;
    }

    function __toString()
    {
        return ''.$this->getTitle();
    }


    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("id")
     * @Serializer\Groups({"listNews"})
     */
    public function getSerializedId()
    {
        return $this->getId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("is_active")
     * @Serializer\Groups({"listNews"})
     */
    public function getSerializedActive()
    {
        return $this->isActive();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("lang_key")
     * @Serializer\Groups({"listNews"})
     */
    public function getSerializedLanguage()
    {

        return $this->getLanguage();

    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("added_date")
     * @Serializer\Groups({"listNews"})
     */
    public function getSerializedDateCreated()
    {

        return $this->getCreatedAt() ? strtotime( date_format($this->getCreatedAt(), 'Y-m-d H:i:s') ) : false;

    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("updated_date")
     * @Serializer\Groups({"listNews"})
     */
    public function getSerializedDateUpdated()
    {

        return $this->getUpdatedAt() ? strtotime( date_format($this->getUpdatedAt(), 'Y-m-d H:i:s') ) : false;

    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("image_url")
     * @Serializer\Groups({"listNews"})
     */
    public function getSerializedPhoto()
    {

        return $this->getPhoto();

    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("title")
     * @Serializer\Groups({"listNews"})
     */
    public function getSerializedTitle()
    {

        return $this->getTitle();

    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("description")
     * @Serializer\Groups({"listNews"})
     */
    public function getSerializedDescription()
    {

        return $this->getDescription();

    }

}

