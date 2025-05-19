<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use NyroDev\NyroCmsBundle\Model\TemplateCategory as TemplateCategoryModel;
use NyroDev\NyroCmsBundle\Repository\Orm\TemplateCategoryRepository;

#[ORM\Entity(repositoryClass: TemplateCategoryRepository::class)]
#[ORM\Table(name: 'template_category')]
#[Gedmo\SoftDeleteable(fieldName: 'deleted', timeAware: false)]
class TemplateCategory extends TemplateCategoryModel
{
    #[ORM\OneToMany(targetEntity: Template::class, mappedBy: 'templateCategory')]
    protected Collection $templates;
}
