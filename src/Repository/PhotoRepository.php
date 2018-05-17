<?php

namespace App\Repository;

use App\Entity\Photo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Photo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Photo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Photo[]    findAll()
 * @method Photo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PhotoRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Photo::class);
    }


    public function next($albumId, $id) {
        $sql = "select min(id) id from photo where id > :id and photo_album_id = :albumId";
        $params["id"] = $id;
        $params["albumId"] = $albumId;
        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result["id"] == null ? 0 : $result["id"];
    }

    public function previous($albumId, $id) {
        $sql = "select max(id) id from photo where id < :id and photo_album_id = :albumId";
        $params["id"] = $id;
        $params["albumId"] = $albumId;
        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result["id"] == null ? 0 : $result["id"];
    }

    public function findByAlbumId($albumId)
    {
        return $this->createQueryBuilder('p')
            ->join('p.photo_album','c')
            ->andWhere('c.id = :val')
            ->setParameter('val', $albumId)
            ->getQuery()
            ->execute()
        ;
    }
}
