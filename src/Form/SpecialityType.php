<?php

namespace App\Form;

use App\Entity\Speciality;
use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SpecialityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('media', FileType::class, [
                'label' => 'Image',
                'required' => false,
                'mapped' => false,
                'error_bubbling' => false,
                'multiple' => false,
                'constraints' => [
                    new Callback([$this, 'validateSize']),
                    new Callback([$this, 'validateMimeTypes']),
                ],
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'name',
                'expanded' => true,
                'multiple' => true,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Speciality::class,
        ]);
    }

    public function validateSize($files, ExecutionContextInterface $context)
    {
        if ($files) {
            $size = 0;
            foreach ($files as $file) {
                $size += $file->getSize();
            }
            if ($size > 2000000) {
                $context->addViolation('The size of the upload file is greater than 2Mo.', [], null);
            }
        }
    }

    public function validateMimeTypes($files, ExecutionContextInterface $context)
    {
        if ($files) {
            foreach ($files as $file) {
                if (!in_array($file->getMimeType(), ['image/png', 'image/jpeg', 'image/gif'])) {
                    $context->addViolation('Invalid file type. Types avalaible : png, jpg, gif.', [], null);
                }
            }
        }
    }
}
