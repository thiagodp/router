<?php

$rules = [
    '@PSR12' => true,

    //
    // Array
    //
    'array_indentation' => true,
    'array_syntax' => [ 'syntax' => 'short' ],

    //
    // Blank lines
    //
    'blank_line_after_namespace' => true,
    'blank_line_after_opening_tag' => false,
    'blank_lines_before_namespace' => [
        'min_line_breaks' => 0,
        'max_line_breaks' => 1,
    ],
    // 'blank_line_before_statement' => [
    //     'statements' => [
    //         'do', 'for', 'foreach', 'if', 'switch', 'try', 'while',
    //     ]
    // ],
    'line_ending' => true,
    'no_blank_lines_after_class_opening' => false,
    'no_extra_blank_lines' => [
        'tokens' => [
            'attribute',
            'break',
            'case',
            'continue',
            // 'curly_brace_block',
            'default',
            'extra',
            'parenthesis_brace_block',
            'return',
            'square_brace_block',
            'switch',
            'throw',
            'use'
            ]
        ],
    'single_line_empty_body' => true,
    'single_line_after_imports' => true,

    //
    // Spaces
    //
    'binary_operator_spaces' => [
        'default' => 'single_space',
        // 'operators' => [
        //     '=>' => 'align',
        // ]
    ],
    'class_definition' => [
        'space_before_parenthesis' => false,
    ],
    'clean_namespace' => true,
    'function_declaration' => [
        'closure_fn_spacing' => 'none',
        'closure_function_spacing' => 'none',
    ],
    'method_argument_space' => [
        'on_multiline' => 'ensure_fully_multiline',
        'keep_multiple_spaces_after_comma' => false
    ],
    'object_operator_without_whitespace' => true,
    // 'spaces_inside_parentheses' => true,
    'trim_array_spaces' => false,
    'whitespace_after_comma_in_array' => true,
    'no_multiline_whitespace_around_double_arrow' => true,
    'no_spaces_after_function_name' => true,
    'no_space_around_double_colon' => true,
    'no_whitespace_before_comma_in_array' => true,
    'no_whitespace_in_blank_line' => true,
    'not_operator_with_space' => true,
    'spaces_inside_parentheses' => [
        'space' => 'single'
    ],

    //
    // Braces
    //
    'braces_position' => [
        'classes_opening_brace' => 'same_line',
        'functions_opening_brace' => 'same_line',
    ],
    'elseif' => false,
    'include' => true,
    'normalize_index_brace' => true,

    //
    // Parentheses
    //
    'no_unneeded_control_parentheses' => false,

    //
    // Other
    //
    'long_to_shorthand_operator' => true,
    'no_closing_tag' => false,
];

return ( new PhpCsFixer\Config() )
    ->setRules($rules)
    ->setRiskyAllowed(true)
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache')
    ->setFinder(PhpCsFixer\Finder::create()->in(__DIR__ . '/src'))
;