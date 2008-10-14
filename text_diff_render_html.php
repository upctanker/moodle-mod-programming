<?php

require_once 'Text/Diff/Renderer.php';

/**
 * @package Text_Diff
 */
class Text_Diff_Renderer_html extends Text_Diff_Renderer {

    /**
     * Number of leading context "lines" to preserve.
     */
    var $_leading_context_lines = 4;

    /**
     * Number of trailing context "lines" to preserve.
     */
    var $_trailing_context_lines = 4;

    function _blockHeader($xbeg, $xlen, $ybeg, $ylen) {
        $this->_x = $xbeg;
        $this->_y = $ybeg;
    }

    function _startBlock($header) {
        return '<table class="diff">' . $header;
    }

    function _endBlock() {
        return '</table>';
    }

    function _lines($lines, $xi = 1, $yi = 1, $op = '', $clazz = 'normal', $nofirst = false, $nolast = false) {
        $r = '';
        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];

            $clazz1 = $clazz;
            if ($i == 0 && $clazz1 != 'normal' && !$nofirst) {
                $clazz1 .= ' first';
            }
            if ($i == count($lines)-1 && $clazz1 != 'normal' && !$nolast) {
                $clazz1 .= ' last';
            }

            $r .= '<tr>';
            $r .= '<th>'.($xi ? $this->_x : '').'</th>';
            $r .= '<th>'.($yi ? $this->_y : '').'</th>';
            $r .= '<th>'.$op.'</th>';
            $r .= '<td class="'.$clazz1.'">'.htmlspecialchars($line).'</td>';
            $r .= '</tr>';

            $this->_x += $xi;
            $this->_y += $yi;
        }
        return $r;
    }

    function _context($lines) {
        return $this->_lines($lines);
    }

    function _added($lines)
    {
        return $this->_lines($lines, 0, 1, '+', 'added');
    }

    function _deleted($lines)
    {
        return $this->_lines($lines, 1, 0, '-', 'deleted');
    }

    function _changed($orig, $final)
    {
        return $this->_lines($orig, 1, 0, '-', 'deleted', false, true) . $this->_lines($final, 0, 1, '+', 'added', true, false);
    }
}

?>
