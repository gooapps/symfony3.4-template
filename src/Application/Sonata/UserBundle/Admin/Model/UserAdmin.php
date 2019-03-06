<?php

namespace Application\Sonata\UserBundle\Admin\Model;

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
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormTypeInterface;

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
        return array_filter(parent::getExportFields(), function ($v) {
            return !in_array($v, ['password', 'salt']);
        });
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
            ->addIdentifier('username')
            ->add('email')
            ->add('groups')
            ->add('enabled', null, ['editable' => true])
            ->add('createdAt')
        ;

        if ($this->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
            $listMapper
                ->add('impersonating', 'string', ['template' => '@SonataUser/Admin/Field/impersonating.html.twig'])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filterMapper): void
    {
        $filterMapper
            ->add('id')
            ->add('username')
            ->add('email')
            ->add('groups')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->with('General')
            ->add('username')
            ->add('email')
            ->end()
            ->with('Groups')
            ->add('groups')
            ->end()
            ->with('Profile')
            ->add('firstname')
            ->add('lastname')
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper): void
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
            ->with('Roles', ['class' => 'col-md-12'])->end()
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
            ->add('email')
            ->add('plainPassword', TextType::class, [
                'required' => (!$this->getSubject() || null === $this->getSubject()->getId()),
            ])
            ->end()
            ->with('Profile')
            ->add('firstname', null, ['required' => false])
            ->add('lastname', null, ['required' => false])
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
            ->with('Roles')
            ->add('realRoles', SecurityRolesType::class, [
                'label' => 'form.label_roles',
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ])
            ->end()
            ->end()
        ;
    }
}
