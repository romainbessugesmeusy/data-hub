<?php
declare(strict_types=1);

namespace Pimcore\Bundle\DataHubBundle\GraphQL\DataObjectType;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\UnionType;
use Pimcore\Bundle\DataHubBundle\GraphQL\ClassTypeDefinitions;
use Pimcore\Bundle\DataHubBundle\GraphQL\Service;
use Pimcore\Bundle\DataHubBundle\GraphQL\Traits\ServiceTrait;
use Pimcore\Model\DataObject;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ObjectTreeType extends UnionType implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    use ServiceTrait;

    /**
     * ObjectTreeType constructor.
     * @param Service $graphQlService
     * @param array $config
     */
    public function __construct(Service $graphQlService, $config = ['name' => 'object_tree'])
    {
        $this->setGraphQLService($graphQlService);
        parent::__construct($config);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getTypes()
    {
        $types = array_values(ClassTypeDefinitions::getAll(true));
        $types[] = $this->getGraphQlService()->getDataObjectTypeDefinition('_object_folder');

        return $types;
    }

    public function resolveType($element, $context, ResolveInfo $info)
    {
        if (!$element) {
            return null;
        }
        $object = DataObject\AbstractObject::getById($element['id']);

        if ($object instanceof DataObject\Folder) {
            return $this->getGraphQlService()->getDataObjectTypeDefinition('_object_folder');
        }

        return ClassTypeDefinitions::get($object->getClass());
    }
}
