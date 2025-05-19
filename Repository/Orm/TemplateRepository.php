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
        if ($row instanceof Template) {
            // No filter for Template
            return $this->createQueryBuilder('t')
                        ->andWhere('t.state = :state')
                            ->setParameter('state', Template::STATE_ACTIVE)
                        ->andWhere('t.content IS NOT NULL')
                        ->addOrderBy('t.title', 'asc')
                        ->getQuery()
                        ->execute();
        }

        $search = '%'.str_replace('\\', '\\\\', '\\'.get_class($row).'%');

        $templates = $this->createQueryBuilder('t')
                        ->andWhere('t.state = :state')
                            ->setParameter('state', Template::STATE_ACTIVE)
                        ->andWhere('t.content IS NOT NULL')
                        ->andWhere('t.enabledFor LIKE :search')
                            ->setParameter('search', $search)
                        ->addOrderBy('t.title', 'asc')
                        ->getQuery()
                        ->execute();

        return array_filter($templates, function (Template $template) use ($row) {
            foreach ($template->getEnabledFor() as $enabledFor) {
                if ($this->isMatchingFor($row, $enabledFor)) {
                    return true;
                }
            }

            return false;
        });
    }

    public function getTemplateDefaultFor(Composable $row): ?Template
    {
        $availableTemplates = $this->getAvailableTemplatesFor($row);

        foreach ($availableTemplates as $template) {
            if ($template->getDefaultFor() && $this->isMatchingFor($row, $template->getDefaultFor())) {
                return $template;
            }
        }

        return null;
    }

    private function isMatchingFor(Composable $row, string $matchingFor): bool
    {
        $class = '\\'.get_class($row);

        $tmp = explode('::', $matchingFor);

        if ($tmp[0] !== $class) {
            return false;
        }

        if (!isset($tmp[1])) {
            return true;
        }

        $fieldFilter = explode('=', $tmp[1]);
        $propertyAccess = PropertyAccess::createPropertyAccessor();

        return $propertyAccess->getValue($row, $fieldFilter[0]) === $fieldFilter[1];
    }
}
