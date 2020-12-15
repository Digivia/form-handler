<?php
/**
 * @author Eric BATARSON <eric.batarson@digivia.fr>
 */

namespace Digivia\FormHandler\Tests\HandlerFactory\TestSet\Form;

use Digivia\FormHandler\Tests\HandlerFactory\TestSet\Model\TestEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Class FormTestType
 * @package Digivia\Tests\HandlerFactory\TestSet\Form
 */
class FormTestType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("name", TextType::class, [
            'constraints' => new Length(['min' => 3])
        ]);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault("data_class", TestEntity::class);
    }
}
