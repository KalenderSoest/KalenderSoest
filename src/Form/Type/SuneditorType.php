<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SuneditorType extends AbstractType
{
    /**
     * Central editor baseline for all rich-text fields in this project.
     * Only common content tags for formatting, links, lists and tables are allowed.
     */
    private const DEFAULT_OPTIONS = [
        'minHeight' => '240px',
        'width' => '100%',
        'defaultUrlProtocol' => 'https://',
        'resizingBar' => true,
        'buttonList' => [
            ['undo', 'redo'],
            ['formatBlock'],
            ['bold', 'italic', 'underline', 'strike', 'removeFormat'],
            ['fontColor', 'hiliteColor'],
            ['align', 'horizontalRule'],
            ['list', 'outdent', 'indent'],
            ['link', 'table'],
            ['codeView', 'showBlocks', 'fullScreen'],
        ],
        'freeCodeViewMode' => true,
        'elementWhitelist' => 'p|br|div|span|strong|b|em|i|u|s|strike|sub|sup|blockquote|pre|code|ul|ol|li|a|table|thead|tbody|tfoot|tr|th|td|caption|colgroup|col|h1|h2|h3|h4|h5|h6|hr',
        'attributeWhitelist' => [
            '*' => 'id|title',
            'a' => 'href|target|rel|title',
            'table' => 'style|border|cellpadding|cellspacing|width',
            'thead' => 'style',
            'tbody' => 'style',
            'tfoot' => 'style',
            'tr' => 'style',
            'th' => 'style|colspan|rowspan|scope|width|height',
            'td' => 'style|colspan|rowspan|width|height',
            'colgroup' => 'span|width',
            'col' => 'span|width',
        ],
        'attributeBlacklist' => [
            '*' => 'onclick|ondblclick|onmousedown|onmouseup|onmouseover|onmousemove|onmouseout|onmouseenter|onmouseleave|onkeydown|onkeypress|onkeyup|onfocus|onblur|onchange|oninput|onsubmit|onload|onerror',
        ],
        'tagStyles' => [
            'table' => 'width|border|border-collapse',
            'thead|tbody|tfoot|tr' => 'background-color|text-align|vertical-align',
            'th|td' => 'width|height|text-align|vertical-align|background-color|border|border-top|border-right|border-bottom|border-left',
            'p|div|span' => 'text-align',
        ],
        'strictMode' => [
            'tagFilter' => true,
            'formatFilter' => true,
            'classFilter' => true,
            'textStyleTagFilter' => true,
            'attrFilter' => true,
            'styleFilter' => true,
        ],
    ];

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'attr' => [],
            'suneditor_options' => [],
        ]);

        $resolver->setAllowedTypes('suneditor_options', 'array');
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $attr = $view->vars['attr'];
        $attr['data-suneditor'] = '1';
        $editorOptions = array_replace_recursive(self::DEFAULT_OPTIONS, $options['suneditor_options']);
        $attr['data-suneditor-options'] = json_encode(
            $editorOptions,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) ?: '{}';

        $existingClass = isset($attr['class']) ? trim((string) $attr['class']) : '';
        $attr['class'] = trim('js-suneditor ' . $existingClass);

        $view->vars['attr'] = $attr;
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'suneditor';
    }
}
