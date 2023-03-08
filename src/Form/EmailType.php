<?php

/*
 * Classe - EmailType
 *
 * Formulaire d'édition d'un logo partenarie
*/

namespace App\Form;

use App\Validator\Constraints\PlaceholderConstraint;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('subject', CKEditorType::class, [
            'label_format' => 'common.subject',
            'config' => [
                'toolbar' => 'subject_mail',
                'placeholder_select' => [
                    'placeholders' => $options['placeholder']
                ],
                'class' => 'ckeditor',
            ],
            'constraints' => [
                new PlaceholderConstraint([
                    'placeholders' => $options['placeholder']
                ]),
            ],
        ])
        ->add('corps', CKEditorType::class, [
            'label_format' => 'common.texte',
            'config' => [
                'placeholder_select' => [
                    'placeholders' => $options['placeholder']
                ],
                'class' => 'ckeditor',
            ],
            'constraints' => [
                new PlaceholderConstraint([
                    'placeholders' => $options['placeholder']
                ]),
            ],
        ])
        ->add('save', SubmitType::class, [
            'label_format' => 'bouton.save',
        ]);

        $builder->get('subject')->addModelTransformer(new CallbackTransformer(
            function ($subjectInDB) { // From DB to Form
                return $subjectInDB;
            },
            function ($subjectInForm) { // From Form to DB
                $subject = strip_tags($subjectInForm);
                $subject = str_replace(
                    ['&nbsp;', '&aacute;', '&agrave;', '&auml;', '&acirc;', '&aring;', '&alpha;', '&atilde;', '&aelig;', '&Aacute;', '&Agrave;', '&Auml;', '&Acirc;', '&Aring;', '&Alpha;', '&Atilde;', '&AElig;', '&beta;', '&Beta;', '&ccedil;', '&cent;', '&chi;', '&Ccedil;', '&cent;', '&Chi;', '&delta;', '&eth;', '&Delta;', '&ETH;', '&eacute;', '&egrave;', '&ecirc;', '&euml;', '&epsilon;', '&eta;', '&euro;', '&Eacute;', '&Egrave;', '&Ecirc;', '&Euml;', '&Epsilon;', '&Eta;', '&fnof;', '&gamma;', '&Gamma;', '&iuml;', '&icirc;', '&iacute;', '&igrave;', '&iota;', '&Iuml;', '&Icirc;', '&Iacute;', '&Igrave;', '&Iota;', '&kappa;', '&Kappa;', '&lambda;', '&Lambda;', '&mu;', '&Mu;', '&ntilde;', '&nu;', '&Ntilde;', '&Nu;', '&ocirc;', '&oacute;', '&ouml;', '&ograve;', '&oslash;', '&otilde;', '&oelig;', '&omega;', '&omicron;', '&Ocirc;', '&Oacute;', '&Ouml;', '&Ograve;', '&Oslash;', '&Otilde;', '&OElig;', '&Omega;', '&Omicron;', '&pi;', '&phi;', '&psi;', '&Pi;', '&Phi;', '&Psi;', '&rho;', '&Rho;', '&scaron;', '&szlig;', '&sigma;', '&Scaron;', '&Sigma;', '&theta;', '&tau;', '&thorn;', '&Theta;', '&Tau;', '&THORN;', '&ucirc;', '&uacute;', '&uuml;', '&ugrave;', '&upsilon;', '&Ucirc;', '&Uacute;', '&Uuml;', '&Ugrave;', '&Upsilon;', '&xi;', '&Xi;', '&yuml;', '&yacute;', '&Yuml;', '&Yacute;', '&zeta;', '&Zeta;', '&sup2;', '&amp;', '&quot;', '&#39;', '&deg;', '&uml;', '&pound;', '&curren;', '&micro;', '&sect;'],
                    [' ', 'á', 'à', 'ä', 'â', 'å', 'α', 'ã', 'æ', 'Á', 'À', 'Ä', 'Â', 'Å', 'Α', 'Ã', 'Æ', 'β', 'Β', 'ç', '¢', 'χ', 'Ç', '¢', 'Χ', 'δ', 'ð', 'Δ', 'Ð', 'é', 'è', 'ê', 'ë', 'ε', 'η', '€', 'É', 'È', 'Ê', 'Ë', 'Ε', 'Η', 'ƒ', 'γ', 'Γ', 'ï', 'î', 'í', 'ì', 'ι', 'Ï', 'Î', 'Í', 'Ì', 'Ι', 'κ', 'Κ', 'λ', 'Λ', 'μ', 'Μ', 'ñ', 'ν', 'Ñ', 'Ν', 'ô', 'ó', 'ö', 'ò', 'ø', 'õ', 'œ', 'ω', 'ο', 'Ô', 'Ó', 'Ö', 'Ò', 'Ø', 'Õ', 'Œ', 'Ω', 'Ο', 'π', 'φ', 'ψ', 'Π', 'Φ', 'Ψ', 'ρ', 'Ρ', 'š', 'ß', 'σ', 'Š', 'Σ', 'θ', 'τ', 'þ', 'Θ', 'Τ', 'Þ', 'û', 'ú', 'ü', 'ù', 'υ', 'Û', 'Ú', 'Ü', 'Ù', 'Υ', 'ξ', 'Ξ', 'ÿ', 'ý', 'Ÿ', 'Ý', 'ζ', 'Ζ', '²', '&', '"', '\'', '°', '¨', '£', '¤', 'µ', '§'],
                    $subject
                );
                $subject = trim($subject);
                return $subject;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Uca\Email',
            'placeholder' => ''
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_email';
    }
}
