<?php
/**
 * 2007-2018 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace EO\Assembly\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Translation\TranslatorInterface;

class AssemblyProductsType extends TranslatorAwareType
{
    /**
     * AssemblyProductsType constructor.
     *
     * @param TranslatorInterface $translator
     * @param array $locales
     */
    public function __construct(TranslatorInterface $translator, array $locales)
    {
        parent::__construct($translator, $locales);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('name', TextType::class, [
                'required' => false,
                'disabled' => true,
            ])
            ->add('assembly_time', NumberType::class, [
                'required' => false,
                'disabled' => true,
                'label' => 'Время сборки (мин.)',
            ])
            ->add('assembly_time_custom', NumberType::class, [
                'required' => false,
                'label' => 'Время сборки ручное значение (мин.)',
            ])
            ->add('calculated_assembly_time', NumberType::class, [
                'required' => false,
                'disabled' => true,
                'label' => 'Автоматический расчет времени сборки (мин.)',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'module_assembly';
    }
}
