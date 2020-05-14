<?php

namespace App\Application\Sonata\UserBundle\Admin;

use FOS\UserBundle\Model\UserManagerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\DatePickerType;
use Sonata\UserBundle\Form\Type\SecurityRolesType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormTypeInterface;
use Sonata\MediaBundle\Form\Type\MediaType;

class UserAdmin extends AbstractAdmin
{
    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * {@inheritdoc}
     */
    public function getFormBuilder()
    {
        $this->formOptions['data_class'] = $this->getClass();

        $options = $this->formOptions;
        $options['validation_groups'] = (!$this->getSubject() || null === $this->getSubject()->getId()) ? 'Registration' : 'Profile';

        $formBuilder = $this->getFormContractor()->getFormBuilder($this->getUniqid(), $options);

        $this->defineFormBuilder($formBuilder);

        return $formBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getExportFields()
    {
        // avoid security field to be exported
        return [
            "Email" => 'email',
            "Nombre" => 'firstname',
            "Apellidos" => 'lastname',
            "Empresa" => 'company',
            "Cargo" => 'companyPosition',

        ];


    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($user): void
    {
        $this->getUserManager()->updateCanonicalFields($user);
        $this->getUserManager()->updatePassword($user);
    }

    /**
     * @param UserManagerInterface $userManager
     */
    public function setUserManager(UserManagerInterface $userManager): void
    {
        $this->userManager = $userManager;
    }

    /**
     * @return UserManagerInterface
     */
    public function getUserManager()
    {
        return $this->userManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('avatar', null, ['label' => "Logo", "template" => "AppBundle:Admin/CRUD:custom_list_image_avatar.html.twig"])
            ->addIdentifier('email')
            ->add('firstname', null, ['label' => "Nombre"])
            ->add('lastname', null, ['label' => "Apellidos"])
            ->add('_action', null, [
                'label' => 'Acciones',
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filterMapper): void
    {
        $filterMapper
            ->add('email')
            ->add('firstname', null, ['label' => "Nombre"])
            ->add('lastname', null, ['label' => "Apellidos"])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('General')
            ->add('firstname', null, ['label' => "Nombre"])
            ->add('lastname', null, ['label' => "Apellidos"])
            ->add('avatar', null, ['label' => "Avatar", "template" => "AppBundle:Admin/CRUD:custom_show_image_avatar.html.twig"])
            ->end()
            ->with('Profile')
            ->add('username', null, ['label' => "Nombre de usuario"])
            ->add('email', null, ['label' => "Email"])
            ;
            if($this->getConfigurationPool()->getContainer()->get('kernel')->getEnvironment() == "dev"){
                $showMapper->add('firebaseToken', null, ['label' => "Token Firebase"]);
            }
        $showMapper
            ->end()
            ->with('Groups')
            ->add('groups')
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        // define group zoning
        $formMapper
            ->tab('User')
            ->with('Profile', ['class' => 'col-md-6'])->end()
            ->with('General', ['class' => 'col-md-6'])->end()
            ->end()
            ->tab('Security')
            ->with('Status', ['class' => 'col-md-6'])->end()
            ->with('Groups', ['class' => 'col-md-6'])->end()
            ->end()
        ;

        $now = new \DateTime();

        $genderOptions = [
            'choices' => call_user_func([$this->getUserManager()->getClass(), 'getGenderList']),
            'required' => true,
            'translation_domain' => $this->getTranslationDomain(),
        ];

        // NEXT_MAJOR: Remove this when dropping support for SF 2.8
        if (method_exists(FormTypeInterface::class, 'setDefaultOptions')) {
            $genderOptions['choices_as_values'] = true;
        }

        $formMapper
            ->tab('User')
            ->with('General')
            ->add('username')
            ->add('email', EmailType::class, ['label' => "Dirección de correo electrónico"])
            ->add('plainPassword', TextType::class, [
                'required' => (!$this->getSubject() || null === $this->getSubject()->getId()),
            ])
            ->end()
            ->with('Profile')
            ->add('firstname', null, ['label' => "Nombre", 'required' => true])
            ->add('lastname', null, ['label' => "Apellidos", 'required' => true])
//            ->add('avatar', MediaType::class, [
//                    'label' => "Logo",
//                    'provider' => 'sonata.media.provider.image',
//                    'context'  => 'default']
//            )
            ->end()
            ->end()
            ->tab('Security')
            ->with('Status')
            ->add('enabled', null, ['required' => false])
            ->end()
            ->with('Groups')
            ->add('groups', ModelType::class, [
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ])
            ->end()
            ->end()
        ;
    }
}
