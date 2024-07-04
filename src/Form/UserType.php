<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'attr' => [
                    'class' => '',
                ],
            ])
            // ->add('roles')
            ->add(
                'plainPassword',
                PasswordType::class,
                [
                    'hash_property_path' => 'password',
                    'mapped' => false,
                    // // instead of being set onto the object directly,
                    // // this is read and encoded in the controller
                    // 'mapped' => false,
                    // 'attr' => ['autocomplete' => 'password'],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Merci de saisir un mot de passe',
                        ]),
                        new Regex(
                            '/^(?=.*[!@#$%^&*-])(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z]).{8,20}$/'),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères.',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]
                    ),
                    ],
                ]
            )
            ->add('nom', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-control rounded-1',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
