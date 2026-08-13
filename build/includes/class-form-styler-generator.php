<?php
namespace Contactum;

/**
 * Turns a form's saved style settings (an array keyed by category) into a
 * plain CSS string scoped to that one form. Ported from the same idea Fluent
 * Forms Pro uses (their FormStylerGenerator), but targeting Contactum's own
 * real frontend markup — every selector below was checked against the
 * actual field render() methods, not assumed.
 */
class FormStylerGenerator {

    /**
     * @param string $selector The form's own scoping selector, e.g. ".contactum_form_123".
     * @param array  $styles   Style settings keyed by category.
     * @return string
     */
    public function generate( $selector, array $styles ) {
        $css = '';

        if ( ! empty( $styles['container'] ) ) {
            $css .= $this->declaration( $selector, $this->box( $styles['container'] ) );
        }

        if ( ! empty( $styles['label'] ) ) {
            $css .= $this->declaration( "{$selector} .contactum-label label", $this->text( $styles['label'] ) );
        }

        if ( ! empty( $styles['placeholder']['color'] ) ) {
            $css .= $this->declaration(
                "{$selector} .contactum-el-form-control::placeholder",
                'color:' . $styles['placeholder']['color'] . ';'
            );
        }

        if ( ! empty( $styles['asterisk']['color'] ) ) {
            $css .= $this->declaration( "{$selector} .required", 'color:' . $styles['asterisk']['color'] . ' !important;' );
        }

        if ( ! empty( $styles['input'] ) ) {
            $css .= $this->generate_input( $selector, $styles['input'] );
        }

        if ( ! empty( $styles['submit_button'] ) ) {
            $css .= $this->generate_button( $selector, '.contactum-submit .btn-submit', $styles['submit_button'], true );
        }

        if ( ! empty( $styles['next_button'] ) ) {
            $css .= $this->generate_button( $selector, '.contactum-step-nav__next', $styles['next_button'], false );
        }

        if ( ! empty( $styles['prev_button'] ) ) {
            $css .= $this->generate_button( $selector, '.contactum-step-nav__prev', $styles['prev_button'], false );
        }

        if ( ! empty( $styles['section_break'] ) ) {
            $css .= $this->generate_section_break( $selector, $styles['section_break'] );
        }

        if ( ! empty( $styles['grid_table'] ) ) {
            $css .= $this->generate_grid_table( $selector, $styles['grid_table'] );
        }

        if ( ! empty( $styles['step_header'] ) ) {
            $css .= $this->generate_step_header( $selector, $styles['step_header'] );
        }

        if ( ! empty( $styles['range_slider'] ) ) {
            $css .= $this->generate_range_slider( $selector, $styles['range_slider'] );
        }

        if ( ! empty( $styles['checkable'] ) ) {
            $css .= $this->generate_checkable( $selector, $styles['checkable'] );
        }

        if ( ! empty( $styles['error_message'] ) ) {
            $css .= $this->declaration( "{$selector} .contactum-error-msg", $this->text( $styles['error_message'] ) );
        }

        if ( ! empty( $styles['success_message'] ) ) {
            $css .= $this->declaration( ".contactum-success", $this->text( $styles['success_message'] ) );
        }

        return trim( $css );
    }

    // ── Per-category generators ─────────────────────────────────────────────

    private function generate_input( $selector, $input ) {
        $css = '';

        if ( ! empty( $input['normal'] ) ) {
            $css .= $this->declaration( "{$selector} .contactum-el-form-control", $this->box( $input['normal'] ) . $this->text( $input['normal'] ) );
        }

        if ( ! empty( $input['focus'] ) ) {
            $css .= $this->declaration( "{$selector} .contactum-el-form-control:focus", $this->box( $input['focus'] ) . $this->text( $input['focus'] ) );
        }

        return $css;
    }

    private function generate_button( $selector, $target, $button, $with_alignment ) {
        $css = '';

        if ( $with_alignment && ! empty( $button['alignment'] ) ) {
            $css .= $this->declaration( "{$selector} .contactum-submit, {$selector} .submit_wrapper", 'text-align:' . $button['alignment'] . ';' );
        }

        if ( ! empty( $button['normal'] ) ) {
            $css .= $this->declaration( "{$selector} {$target}", $this->box( $button['normal'] ) . $this->text( $button['normal'] ) );
        }

        if ( ! empty( $button['hover'] ) ) {
            $css .= $this->declaration( "{$selector} {$target}:hover", $this->box( $button['hover'] ) . $this->text( $button['hover'] ) );
        }

        return $css;
    }

    private function generate_section_break( $selector, $section ) {
        $css = '';

        if ( ! empty( $section['title'] ) ) {
            $css .= $this->declaration( "{$selector} .section-title", $this->text( $section['title'] ) );
        }

        if ( ! empty( $section['description'] ) ) {
            $css .= $this->declaration( "{$selector} .section-details", $this->text( $section['description'] ) );
        }

        return $css;
    }

    private function generate_grid_table( $selector, $grid ) {
        $css = '';

        if ( ! empty( $grid['head'] ) ) {
            $css .= $this->declaration( "{$selector} .contactum-grid-table thead th", $this->box( $grid['head'] ) . $this->text( $grid['head'] ) );
        }

        if ( ! empty( $grid['body'] ) ) {
            $css .= $this->declaration( "{$selector} .contactum-grid-table tbody td", $this->box( $grid['body'] ) . $this->text( $grid['body'] ) );
        }

        return $css;
    }

    private function generate_step_header( $selector, $step ) {
        $active   = ! empty( $step['active_color'] ) ? $step['active_color'] : '';
        $inactive = ! empty( $step['inactive_color'] ) ? $step['inactive_color'] : '#e5e7eb';
        $text     = ! empty( $step['text_color'] ) ? $step['text_color'] : '#4b5563';

        if ( ! $active ) {
            return '';
        }

        return "{$selector} .contactum-step-progress__bar { background:{$inactive}; } "
            . "{$selector} .contactum-step-progress__bar span { background:{$active}; } "
            . "{$selector} .contactum-step-progress__label { color:{$text}; } ";
    }

    private function generate_range_slider( $selector, $range ) {
        if ( empty( $range['active_color'] ) ) {
            return '';
        }

        $active   = $range['active_color'];
        $inactive = ! empty( $range['inactive_color'] ) ? $range['inactive_color'] : '#e5e7eb';
        $text     = ! empty( $range['text_color'] ) ? $range['text_color'] : '#374151';

        return "{$selector} .contactum-range-slider { accent-color:{$active}; } "
            . "{$selector} .contactum-range-slider-wrap { --track-color:{$inactive}; } "
            . "{$selector} .contactum-range-output { color:{$text}; } ";
    }

    private function generate_checkable( $selector, $checkable ) {
        $css = '';

        if ( ! empty( $checkable['color'] ) ) {
            $css .= "{$selector} .contactum-fields label { color:{$checkable['color']}; } ";
        }

        if ( ! empty( $checkable['active_color'] ) ) {
            $css .= "{$selector} input[type=checkbox]:checked, {$selector} input[type=radio]:checked { accent-color:{$checkable['active_color']}; } ";
        }

        return $css;
    }

    // ── Shared style-block builders ─────────────────────────────────────────

    /**
     * Box-model + visual properties shared by container/input/button/grid
     * cells: background, padding, margin, border, border-radius, box-shadow.
     */
    private function box( $style ) {
        $css = '';

        if ( ! empty( $style['background'] ) ) {
            $css .= 'background-color:' . $style['background'] . ';';
        }

        if ( ! empty( $style['padding'] ) ) {
            $css .= $this->dimensions( 'padding', $style['padding'] );
        }

        if ( ! empty( $style['margin'] ) ) {
            $css .= $this->dimensions( 'margin', $style['margin'] );
        }

        if ( ! empty( $style['border'] ) && ! empty( $style['border']['status'] ) ) {
            $border = $style['border'];

            if ( ! empty( $border['color'] ) ) {
                $css .= 'border-color:' . $border['color'] . ';';
            }
            $css .= 'border-style:' . ( ! empty( $border['type'] ) ? $border['type'] : 'solid' ) . ';';

            if ( ! empty( $border['width'] ) ) {
                $css .= $this->dimensions( 'border', $border['width'], '-width' );
            }

            if ( ! empty( $border['radius'] ) ) {
                $css .= $this->radius( $border['radius'] );
            }
        }

        if ( ! empty( $style['boxshadow'] ) && ! empty( $style['boxshadow']['color'] ) ) {
            $shadow = $style['boxshadow'];
            $inset  = ! empty( $shadow['inset'] ) ? ' inset' : '';
            $css   .= sprintf(
                'box-shadow:%dpx %dpx %dpx %dpx %s%s;',
                (int) ( $shadow['x'] ?? 0 ),
                (int) ( $shadow['y'] ?? 0 ),
                (int) ( $shadow['blur'] ?? 0 ),
                (int) ( $shadow['spread'] ?? 0 ),
                $shadow['color'],
                $inset
            );
        }

        return $css;
    }

    /**
     * Text/typography properties shared by label/section-title/messages/etc:
     * color + font-size/weight/style/transform/decoration/line-height/letter-spacing.
     */
    private function text( $style ) {
        $css = '';

        if ( ! empty( $style['color'] ) ) {
            $css .= 'color:' . $style['color'] . ';';
        }

        if ( empty( $style['typography'] ) ) {
            return $css;
        }

        $t = $style['typography'];

        if ( ! empty( $t['font_size'] ) ) {
            $css .= 'font-size:' . (int) $t['font_size'] . 'px;';
        }
        if ( ! empty( $t['font_weight'] ) ) {
            $css .= 'font-weight:' . $t['font_weight'] . ';';
        }
        if ( ! empty( $t['font_style'] ) ) {
            $css .= 'font-style:' . $t['font_style'] . ';';
        }
        if ( ! empty( $t['text_transform'] ) ) {
            $css .= 'text-transform:' . $t['text_transform'] . ';';
        }
        if ( ! empty( $t['text_decoration'] ) ) {
            $css .= 'text-decoration:' . $t['text_decoration'] . ';';
        }
        if ( isset( $t['line_height'] ) && '' !== $t['line_height'] ) {
            $css .= 'line-height:' . (float) $t['line_height'] . ';';
        }
        if ( isset( $t['letter_spacing'] ) && '' !== $t['letter_spacing'] ) {
            $css .= 'letter-spacing:' . (float) $t['letter_spacing'] . 'px;';
        }

        return $css;
    }

    /**
     * Per-side box-model dimensions (padding/margin/border-width), respecting
     * a "linked" flag that applies the top value to all four sides.
     */
    private function dimensions( $property, $values, $suffix = '' ) {
        if ( ! empty( $values['linked'] ) ) {
            $top = isset( $values['top'] ) && '' !== $values['top'] ? $values['top'] : 0;
            return "{$property}{$suffix}:{$top}px;";
        }

        $css = '';
        foreach ( [ 'top', 'right', 'bottom', 'left' ] as $side ) {
            if ( isset( $values[ $side ] ) && '' !== $values[ $side ] ) {
                $css .= "{$property}-{$side}{$suffix}:" . (float) $values[ $side ] . 'px;';
            }
        }

        return $css;
    }

    private function radius( $values ) {
        if ( ! empty( $values['linked'] ) ) {
            $top = isset( $values['top'] ) && '' !== $values['top'] ? $values['top'] : 0;
            return "border-radius:{$top}px;";
        }

        $map = [
            'top'    => 'border-top-left-radius',
            'right'  => 'border-top-right-radius',
            'bottom' => 'border-bottom-right-radius',
            'left'   => 'border-bottom-left-radius',
        ];

        $css = '';
        foreach ( $map as $side => $prop ) {
            if ( isset( $values[ $side ] ) && '' !== $values[ $side ] ) {
                $css .= "{$prop}:" . (float) $values[ $side ] . 'px;';
            }
        }

        return $css;
    }

    private function declaration( $selector, $body ) {
        $body = trim( $body );
        return $body ? "{$selector} { {$body} } " : '';
    }
}
