<?php

/*
 * classe - Annotation
 *
 * Service gérant les annotations
*/

namespace App\Service\Common;

use App\Entity\Uca\Annotation as EntityAnnotation;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;

class Annotation
{
    private $em;

    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    public function getMetadatas($domainFilter = null)
    {
        $allMetadatas = $this->em->getMetadataFactory()->getAllMetadata();
        $filteredMetadatas = $allMetadatas;
        if (!empty($domainFilter)) {
            $filteredMetadatas = array_filter($allMetadatas, function ($item) use ($domainFilter) {
                return substr($item->getName(), 0, strlen($domainFilter)) === $domainFilter;
            });
        }

        return $filteredMetadatas;
    }

    public function getAnnotations($property, $annotationFilter = null)
    {
        $reader = new AnnotationReader();
        $allAnnotations = $reader->getPropertyAnnotations($property);
        $filteredAnnotations = $allAnnotations;
        if (!empty($annotationFilter)) {
            $filteredAnnotations = array_filter($allAnnotations, function ($item) use ($annotationFilter) {
                return get_class($item) == $annotationFilter;
            });
        }

        return $filteredAnnotations;
    }

    public function truncateTable($tableName)
    {
        $connection = $this->em->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeUpdate($platform->getTruncateTableSQL($tableName));
    }

    public function loadEntityAnnotation($domainFilter = null, $annotationFilter = null)
    {
        $this->truncateTable('ext_annotation');
        $properties = [];
        $filteredMetadatas = $this->getMetadatas($domainFilter);

        foreach ($filteredMetadatas as $metadata) {
            $rc = new \ReflectionClass($metadata->getName());
            foreach ($rc->getProperties() as $property) {
                $filteredAnnotations = $this->getAnnotations($property, $annotationFilter);
                foreach ($filteredAnnotations as $annotation) {
                    $row = ['entity' => $metadata->getName(), 'field' => $property->getName(), 'annotation' => get_class($annotation)];
                    $properties[] = $row;
                    $p = new EntityAnnotation($row);
                    $this->em->persist($p);
                }
            }
        }
        $this->em->flush();

        return $properties;
    }
}
