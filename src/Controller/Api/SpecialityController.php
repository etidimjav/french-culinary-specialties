<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Speciality;
use App\Repository\SpecialityRepository;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SpecialityController extends AbstractFOSRestController
{
    /**
     * @var SpecialityRepository
     */
    private $repository;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(
        SpecialityRepository $repository,
        ValidatorInterface $validator
    ) {
        $this->repository = $repository;
        $this->validator = $validator;
    }

    /**
     * @Rest\Get(
     *    path = "/specialities",
     *    host="api.%domain%",
     *    name = "app_specialities_show"
     * )
     */
    public function getSpecialitiesAction()
    {
        $data = $this->repository->findAll();
        $view = $this->view($data, 200);

        return $this->handleView($view);
    }

    /**
     * @Rest\Get(
     *    path = "/specialities_filter",
     *    host="api.%domain%",
     *    name = "app_specialities_filter_show"
     * )
     * @QueryParam(name="name", requirements="[A-Za-zéèàê ]+", description="Speciality name.")
     * @QueryParam(map=true, name="tags", requirements="\d+", description="Tag name.")
     */
    public function getSpecialitiesFilterAction(ParamFetcher $paramFetcher)
    {
        // $dynamicRequestParam = new RequestParam();
        // $dynamicRequestParam->name = "dynamic_request";
        // $dynamicRequestParam->requirements = "[a-z]+";
        // $paramFetcher->addParam($dynamicRequestParam);

        $dynamicQueryParam = new QueryParam();
        $dynamicQueryParam->name = 'dynamic_query';
        $dynamicQueryParam->requirements = '[A-Za-zéèàê ]+';
        $paramFetcher->addParam($dynamicQueryParam);

        $name = $paramFetcher->get('name');
        $tags = $paramFetcher->get('tags');

        $filters = [];
        if (!empty($name)) {
            $filters['name'] = $name;
        }
        if (!empty($tags)) {
            $filters['tags'] = $tags;
        }

        $data = $this->repository->findByFilters($filters);
        $view = $this->view($data, 200);
        $this->addSerializationGroups($view);

        return $this->handleView($view);
    }

    /**
     * @Rest\Get(
     *    path = "/specialities/{id}",
     *    host="api.%domain%",
     *    name = "app_speciality_show",
     *    requirements = {"id"="\d+"}
     * )
     */
    public function getSpecialityAction(int $id, Request $request): Response
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
     *    path = "/specialities",
     *    host="api.%domain%",
     *    name = "app_speciality_create"
     * )
     */
    public function postSpecialityAction(Request $request): Response
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
     *    path = "/specialities/{id}",
     *    host="api.%domain%",
     *    name = "app_speciality_update",
     *    requirements = {"id"="\d+"}
     * )
     */
    public function putSpecialityAction(int $id, Request $request): Response
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
     *    path = "/specialities/{id}",
     *    host="api.%domain%",
     *    name = "app_speciality_delete"
     * )
     */
    public function deleteSpecialityAction(int $id, Request $request): Response
    {
        $entity = $this->load($id, $request);

        $this->remove($id);

        return $this->handleView($this->view());
    }

    protected function mapDataToEntity(array $data, Speciality $entity): void
    {
        $entity->setName($data['name']);
        $entity->setMedia($data['media']);

        if ($newTags = $data['tags'] ?? null) {
            //gestion des tags
            $tags = $entity->getTags();
            $existingTagIds = [];
            $newTagIds = [];
            //id soumis
            foreach ($newTags as $id) {
                if (isset($id)) {
                    $newTagIds[] = $id;
                }
            }
            //id des tags existants
            foreach ($tags as $tag) {
                $id = $tag->getId();
                $existingTagIds[] = $id;
            }
            //remove
            foreach ($tags as $tag) {
                $id = $tag->getId();
                if ($id && !in_array($id, $newTagIds)) {
                    $entity->removeTag($tag);
                }
            }
            //add
            foreach ($newTagIds as $id) {
                if ($id && !in_array($id, $existingTagIds)) {
                    $tag = $this->tagRepository->findById($id);
                    if ($tag) {
                        $entity->addTag($tag);
                    }
                }
            }
        }
    }

    protected function load(int $id, Request $request): ?Speciality
    {
        return $this->repository->findById($id);
    }

    protected function create(Request $request): Speciality
    {
        return $this->repository->create();
    }

    protected function save(Speciality $entity): void
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
        $view->setContext($context->setGroups(['Default', 'fullSpeciality']));
    }
}
