<?php

/*
 * Classe - ShnuHighlight:
 *
 * Permet d'enregistrer les highlight du sport de haut niveau
 * Les éléments sotn organisables.
*/

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use UcaBundle\Annotations\CKEditor;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\ShnuHighlightRepository")
 * @Vich\Uploadable
 * @Gedmo\Loggable
 * @ORM\EntityListeners({"UcaBundle\Service\Listener\Entity\ShnuHighlightListener"})
 */
class ShnuHighlight
{
    //region Propriétés

    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ordre;

    /**
     * @Gedmo\Translatable
     * @Gedmo\Versioned
     * @ORM\Column(type="string", nullable=true)
     */
    private $titre;

    /**
     * @Gedmo\Translatable
     * @Gedmo\Versioned
     * @ORM\Column(type="text", nullable=true)
     * @CKEditor
     */
    private $texte;

    /** @ORM\Column(type="text")
     * @Assert\NotBlank(message="highlight.urlvideo.notblank")
     * @Assert\Url(message="highlight.urlvideo.invalid")
     * @Assert\Regex(
     *      pattern="(youtube|dailymotion|vimeo|facebook|instagram)",
     *      match=true,
     *      message="highlight.urlvideo.invalid"
     * )
     */
    private $video;

    /** @ORM\Column(type="text", nullable=true)*/
    private $miniature;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $updatedAt;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $image;

    /** @Vich\UploadableField(mapping="map_image", fileNameProperty="image")
     * @Assert\File(
     *     mimeTypes = {"image/png", "image/jpeg", "image/tiff"},
     *     mimeTypesMessage = "activite.image.format"
     * )
     */
    private $imageFile;

    /** @ORM\Column(type="string", nullable=true) */
    private $lecteurVideo;

    /** @ORM\Column(type="string", nullable=true) */
    private $intervenant;

    /** @ORM\Column(type="string", nullable=true) */
    private $height;

    //endregion

    // region methods
    public function getImageUrl()
    {
        if (null != $this->getMiniature()) {
            $imageUrl = $this->getMiniature();
        } elseif (null != $this->getImage()) {
            $imageUrl = $this->getImage();
        } else {
            $imageUrl = '';
        }

        return $imageUrl;
    }

    public function setImageFile(File $imageFile = null)
    {
        $this->imageFile = $imageFile;
        if ($imageFile) {
            $this->updatedAt = new \DateTime('now');
        }

        return $this;
    }

    public function setVideo($video)
    {
        if (true == strpos($video, 'youtube')) { //Si la vidéo provient de youtube on convertit l'url afin que l'on puisse le lire dans l'iframe
            $this->setLecteurVideo('youTube');
            $url = 'https://www.youtube.com/embed/';
            $idVideo = $this->getIdVideo($video);
            $this->video = $url.$idVideo;
            $thumbnail = 'http://img.youtube.com/vi/<YouTube_Video_ID_HERE>/hqdefault.jpg';
            $this->setMiniature(str_replace('<YouTube_Video_ID_HERE>', $idVideo, $thumbnail));
        } elseif (true == strpos($video, 'dailymotion')) { //Si la vidéo provient de Dailymotion
            $this->setLecteurVideo('dailymotion');
            $url = 'https://dailymotion.com/embed/video/';
            $idVideo = $this->getIdVideo($video);
            $this->video = $url.$idVideo;
            $thumbnail = 'https://www.dailymotion.com/thumbnail/video/<Dailymotion_ID_HERE>';
            $this->setMiniature(str_replace('<Dailymotion_ID_HERE>', $idVideo, $thumbnail));
        } elseif (true == strpos($video, 'vimeo')) { //Si la vidéo provient de Vimeo
            $this->setLecteurVideo('vimeo');
            $url = 'https://player.vimeo.com/video/';
            $idVideo = $this->getIdVideo($video);
            $this->video = $url.$idVideo;
            $data = file_get_contents("http://vimeo.com/api/v2/video/{$idVideo}.json");
            $data = json_decode($data);
            $this->setMiniature($data[0]->thumbnail_large);
        } elseif (true == strpos($video, 'facebook')) {
            $this->setLecteurVideo('facebook');
            $this->video = $this->getDataFacebook($video)['urlVideo'];
        } elseif (true == strpos($video, 'instagram')) {
            $this->setLecteurVideo('instagram');
            $dataInstagram = $this->getDataInstagram($video);
            $this->video = $dataInstagram['urlVideo'];
            $this->setMiniature($dataInstagram['urlThumb']);
            $this->setHeight($dataInstagram['height']);
        } else { //Sinon on convertit on met directement le lien
            $this->video = $video;
        }

        return $this;
    }

    public function getIdVideo($url)
    {
        $params = explode('/', $url);
        if ('' == $params[array_key_last($params)]) {
            unset($params[array_key_last($params)]);
        }
        $idVideo = explode('&', $params[array_key_last($params)]);

        if (true == strpos($url, 'youtube')) {
            $idVideo = str_replace('watch?v=', '', $idVideo[0]);
        } else {
            $idVideo = $idVideo[0];
        }

        return $idVideo;
    }

    public function getDataFacebook($video)
    {
        $idVideo = $this->getIdVideo($video);

        return  [
            'urlVideo' => 'https://www.facebook.com/plugins/video.php?href='.urlencode($video),
            'urlThumb' => 'https://graph.facebook.com/'.$idVideo.'/picture',
        ];
    }

    public function getDataInstagram($video)
    {
        $idVideo = (explode('/', preg_split('/(\/p\/|\/tv\/)/', $video)[1]))[0];
        $data = file_get_contents('https://api.instagram.com/oembed/?url=http://instagram.com/p/'.$idVideo);
        $data = json_decode($data, true);

        return  [
            'urlVideo' => 'https://instagram.com/p/'.$idVideo.'/embed',
            'urlThumb' => $data['thumbnail_url'],
            'height' => $data['thumbnail_height'],
        ];
    }

    // endregion

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set ordre.
     *
     * @param int $ordre
     *
     * @return ShnuHighlight
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre.
     *
     * @return null|int
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * Set titre.
     *
     * @param string $titre
     *
     * @return ShnuHighlight
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre.
     *
     * @return string
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set texte.
     *
     * @param string $texte
     *
     * @return ShnuHighlight
     */
    public function setTexte($texte)
    {
        $this->texte = $texte;

        return $this;
    }

    /**
     * Get texte.
     *
     * @return string
     */
    public function getTexte()
    {
        return $this->texte;
    }

    /**
     * Get video.
     *
     * @return string
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * Set updatedAt.
     *
     * @param null|\DateTime $updatedAt
     *
     * @return ShnuHighlight
     */
    public function setUpdatedAt($updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     *
     * @return null|\DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set miniature.
     *
     * @param string $miniature
     *
     * @return ShnuHighlight
     */
    public function setMiniature($miniature)
    {
        $this->miniature = $miniature;

        return $this;
    }

    /**
     * Get miniature.
     *
     * @return string
     */
    public function getMiniature()
    {
        return $this->miniature;
    }

    /**
     * Get imageFile.
     *
     * @return null|UcaBundle\Entity\File
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * Set image.
     *
     * @param string $image
     *
     * @return ShnuHighlight
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image.
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set lecteurVideo.
     *
     * @param null|string $lecteurVideo
     *
     * @return ShnuHighlight
     */
    public function setLecteurVideo($lecteurVideo = null)
    {
        $this->lecteurVideo = $lecteurVideo;

        return $this;
    }

    /**
     * Get lecteurVideo.
     *
     * @return null|string
     */
    public function getLecteurVideo()
    {
        return $this->lecteurVideo;
    }

    /**
     * Set intervenant.
     *
     * @param null|string $intervenant
     *
     * @return ShnuHighlight
     */
    public function setIntervenant($intervenant = null)
    {
        $this->intervenant = $intervenant;

        return $this;
    }

    /**
     * Get intervenant.
     *
     * @return null|string
     */
    public function getIntervenant()
    {
        return $this->intervenant;
    }

    /**
     * Set height.
     *
     * @param null|string $height
     *
     * @return ShnuHighlight
     */
    public function setHeight($height = null)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height.
     *
     * @return null|string
     */
    public function getHeight()
    {
        return $this->height;
    }
}
