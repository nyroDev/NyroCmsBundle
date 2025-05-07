<?php

namespace NyroDev\NyroCmsBundle\Repository\Orm;

use Doctrine\ORM\EntityRepository;
use NyroDev\NyroCmsBundle\Model\Composable;
use NyroDev\NyroCmsBundle\Model\Template;
use NyroDev\NyroCmsBundle\Repository\TemplateRepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class TemplateRepository extends EntityRepository implements TemplateRepositoryInterface
{
    public function getAvailableTemplatesFor(Composable $row): array
    {
        return $this->createQueryBuilder('t')
                        ->andWhere('t.state = :state')
                            ->setParameter('state', Template::STATE_ACTIVE)
                        ->andWhere('t.content IS NOT NULL')
                        ->addOrderBy('t.title', 'asc')
                        ->getQuery()
                        ->execute();
    }

    public function getTemplateDefaultFor(Composable $row): ?Template
    {
        $searchPrefix = str_replace('\\', '\\\\', '\\'.get_class($row).'%');

        $templates = $this->createQueryBuilder('t')
                        ->andWhere('t.state = :state')
                            ->setParameter('state', Template::STATE_ACTIVE)
                        ->andWhere('t.content IS NOT NULL')
                        ->andWhere('t.defaultFor LIKE :searchPrefix')
                            ->setParameter('searchPrefix', $searchPrefix)
                        ->getQuery()
                        ->getResult();

        $propertyAccess = PropertyAccess::createPropertyAccessor();
        $matchingTemplate = null;
        foreach ($templates as $template) {
            $tmp = explode('::', $template->getDefaultFor());
            if (isset($tmp[1])) {
                $fieldFilter = explode('=', $tmp[1]);
                if ($propertyAccess->getValue($row, $fieldFilter[0]) !== $fieldFilter[1]) {
                    continue;
                }
            }

            if (
                !$matchingTemplate
                || strlen($matchingTemplate->getDefaultFor()) <= strlen($template->getDefaultFor())
            ) {
                $matchingTemplate = $template;
            }
        }

        return $matchingTemplate;
    }
}
