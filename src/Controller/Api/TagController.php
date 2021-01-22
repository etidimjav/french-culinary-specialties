<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Tag;
use App\Repository\TagRepository;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TagController extends AbstractFOSRestController
{
    /**
     * @var TagRepository
     */
    private $repository;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(
        TagRepository $repository,
        ValidatorInterface $validator
    ) {
        $this->repository = $repository;
        $this->validator = $validator;
    }

    /**
     * @Rest\Get(
     *    path = "/tags",
     *    host="api.%domain%",
     *    name = "app_tags_show"
     * )
     */
    public function getTagsAction()
    {
        $data = $this->repository->findAll();
        $view = $this->view($data, 200);

        return $this->handleView($view);
    }

    /**
     * @Rest\Get(
     *    path = "/tags/{id}",
     *    host="api.%domain%",
     *    name = "app_tag_show",
     *    requirements = {"id"="\d+"}
     * )
     */
    public function getTagAction(int $id, Request $request): Response
    {
        $entity = $this->load($id, $request);

        if (!$entity) {
            throw new NotFoundHttpException();
        }

        $view = $this->view($entity);
        $this->addSerializationGroups($view);

        return $this->handleView($view);
    }

    /**
     * @Rest\Post(
     *    path = "/tags",
     *    host="api.%domain%",
     *    name = "app_tag_create"
     * )
     */
    public function postTagAction(Request $request): Response
    {
        $entity = $this->create($request);

        $this->mapDataToEntity($request->request->all(), $entity);

        $errors = $this->validator->validate($entity);
        if (count($errors) > 0) {
            $errorsMessage = '';
            foreach ($errors as $error) {
                $errorsMessage .= $error->getMessage().'/n';
            }

            throw new \Exception($errorsMessage);
        }

        $this->save($entity);

        return $this->handleView($this->view($entity));
    }

    /**
     * @Rest\Put(
     *    path = "/tags/{id}",
     *    host="api.%domain%",
     *    name = "app_tag_update",
     *    requirements = {"id"="\d+"}
     * )
     */
    public function putTagAction(int $id, Request $request): Response
    {
        $entity = $this->load($id, $request);

        if (!$entity) {
            throw new NotFoundHttpException();
        }

        $this->mapDataToEntity($request->request->all(), $entity);

        $errors = $this->validator->validate($entity);
        if (count($errors) > 0) {
            $errorsMessage = '';
            foreach ($errors as $error) {
                $errorsMessage .= $error->getMessage().'/n';
            }

            throw new \Exception($errorsMessage);
        }

        $this->save($entity);

        return $this->handleView($this->view($entity));
    }

    /**
     * @Rest\Delete(
     *    path = "/tags/{id}",
     *    host="api.%domain%",
     *    name = "app_tag_delete"
     * )
     */
    public function deleteTagAction(int $id, Request $request): Response
    {
        $entity = $this->load($id, $request);

        $this->remove($id);

        return $this->handleView($this->view());
    }

    protected function mapDataToEntity(array $data, Tag $entity): void
    {
        $entity->setName($data['name']);
    }

    protected function load(int $id, Request $request): ?Tag
    {
        return $this->repository->findById($id);
    }

    protected function create(Request $request): Tag
    {
        return $this->repository->create();
    }

    protected function save(Tag $entity): void
    {
        $this->repository->save($entity);
    }

    protected function remove(int $id): void
    {
        $this->repository->remove($id);
    }

    /**
     * Adds the necessary serialization groups to the given view.
     *
     * @param mixed $view
     */
    private function addSerializationGroups($view)
    {
        $context = new Context();

        // set serialization groups
        $view->setContext($context->setGroups(['Default', 'fullTag']));
    }
}
