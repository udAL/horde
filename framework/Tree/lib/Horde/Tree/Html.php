<?php
/**
 * The Horde_Tree_Html:: class extends the Horde_Tree class to provide
 * HTML specific rendering functions.
 *
 * Copyright 2003-2010 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author   Marko Djukic <marko@oblo.com>
 * @category Horde
 * @package  Horde_Tree
 */
class Horde_Tree_Html extends Horde_Tree
{
    /**
     * TODO
     *
     * @var array
     */
    protected $_nodes = array();

    /**
     * TODO
     *
     * @var array
     */
    protected $_node_pos = array();

    /**
     * TODO
     *
     * @var array
     */
    protected $_dropline = array();

    /**
     * Current value of the alt tag count.
     *
     * @var integer
     */
    protected $_alt_count = 0;

    /**
     * Returns the tree.
     *
     * @param boolean $static  If true the tree nodes can't be expanded and
     *                         collapsed and the tree gets rendered expanded.
     *
     * @return string  The HTML code of the rendered tree.
     */
    public function getTree($static = false)
    {
        $this->_static = $static;
        $this->_buildIndents($this->_root_nodes);

        $tree = $this->_buildHeader();
        foreach ($this->_root_nodes as $node_id) {
            $tree .= $this->_buildTree($node_id);
        }
        return $tree;
    }

    /**
     * Returns the HTML code for a header row, if necessary.
     *
     * @return string  The HTML code of the header row or an empty string.
     */
    protected function _buildHeader()
    {
        if (!count($this->_header)) {
            return '';
        }

        $html = '<div';
        /* If using alternating row shading, work out correct
         * shade. */
        if ($this->getOption('alternate')) {
            $html .= ' class="item' . $this->_alt_count . '"';
            $this->_alt_count = 1 - $this->_alt_count;
        }
        $html .= '>';

        foreach ($this->_header as $header) {
            $html .= '<div class="leftFloat';
            if (!empty($header['class'])) {
                $html .= ' ' . $header['class'];
            }
            $html .= '"';

            $style = '';
            if (!empty($header['width'])) {
                $style .= 'width:' . $header['width'] . ';';
            }
            if (!empty($header['align'])) {
                $style .= 'text-align:' . $header['align'] . ';';
            }
            if (!empty($style)) {
                $html .= ' style="' . $style . '"';
            }
            $html .= '>';
            $html .= empty($header['html']) ? '&nbsp;' : $header['html'];
            $html .= '</div>';
        }

        return $html . '</div>';
    }

    /**
     * Recursive function to walk through the tree array and build the output.
     *
     * @param string $node_id  The Node ID.
     *
     * @return string  The tree rendering.
     */
    protected function _buildTree($node_id)
    {
        $output = $this->_buildLine($node_id);

        if (isset($this->_nodes[$node_id]['children']) &&
            $this->_nodes[$node_id]['expanded']) {
            $num_subnodes = count($this->_nodes[$node_id]['children']);
            for ($c = 0; $c < $num_subnodes; $c++) {
                $child_node_id = $this->_nodes[$node_id]['children'][$c];
                $this->_node_pos[$child_node_id] = array();
                $this->_node_pos[$child_node_id]['pos'] = $c + 1;
                $this->_node_pos[$child_node_id]['count'] = $num_subnodes;
                $output .= $this->_buildTree($child_node_id);
            }
        }

        return $output;
    }

    /**
     * Function to create a single line of the tree.
     *
     * @param string $node_id  The Node ID.
     *
     * @return string  The rendered line.
     */
    protected function _buildLine($node_id)
    {
        $className = 'treeRow';
        if (!empty($this->_nodes[$node_id]['class'])) {
            $className .= ' ' . $this->_nodes[$node_id]['class'];
        }
        /* If using alternating row shading, work out correct
         * shade. */
        if ($this->getOption('alternate')) {
            $className .= ' item' . $this->_alt_count;
            $this->_alt_count = 1 - $this->_alt_count;
        }

        $line = '<div class="' . $className . '">';

        /* If we have headers, track which logical "column" we're in
         * for any given cell of content. */
        $column = 0;

        if (isset($this->_nodes[$node_id]['extra'][self::EXTRA_LEFT])) {
            $extra = $this->_nodes[$node_id]['extra'][self::EXTRA_LEFT];
            $cMax = count($extra);
            for ($c = 0; $c < $cMax; $c++) {
                $style = '';
                if (isset($this->_header[$column]['width'])) {
                    $style .= 'width:' . $this->_header[$column]['width'] . ';';
                }

                $line .= '<div class="leftFloat"';
                if (!empty($style)) {
                    $line .= ' style="' . $style . '"';
                }
                $line .= '>' . $extra[$c] . '</div>';

                $column++;
            }
        }

        $style = '';
        if (isset($this->_header[$column]['width'])) {
            $style .= 'width:' . $this->_header[$column]['width'] . ';';
        }
        $line .= '<div class="leftFloat"';
        if (!empty($style)) {
            $line .= ' style="' . $style . '"';
        }
        $line .= '>';

        if ($this->getOption('multiline')) {
            $line .= '<table cellspacing="0"><tr><td>';
        }

        for ($i = $this->_static ? 1 : 0; $i < $this->_nodes[$node_id]['indent']; $i++) {
            $line .= '<img src="' . $this->_img_dir . '/';
            if ($this->_dropline[$i] && $this->getOption('lines', false, true)) {
                $line .= $this->_images['line'] . '" '
                    . 'alt="|&nbsp;&nbsp;&nbsp;" ';
            } else {
                $line .= $this->_images['blank'] . '" '
                    . 'alt="&nbsp;&nbsp;&nbsp;" ';
            }
            $line .= 'height="20" width="20" style="vertical-align:middle" />';
        }
        $line .= $this->_setNodeToggle($node_id) . $this->_setNodeIcon($node_id);
        if ($this->getOption('multiline')) {
            $line .= '</td><td>';
        }
        $line .= $this->_setLabel($node_id);

        if ($this->getOption('multiline')) {
            $line .= '</td></tr></table>';
        }

        $line .= '</div>';
        $column++;

        if (isset($this->_nodes[$node_id]['extra'][self::EXTRA_RIGHT])) {
            $extra = $this->_nodes[$node_id]['extra'][self::EXTRA_RIGHT];
            $cMax = count($extra);
            for ($c = 0; $c < $cMax; $c++) {
                $style = '';
                if (isset($this->_header[$column]['width'])) {
                    $style .= 'width:' . $this->_header[$column]['width'] . ';';
                }

                $line .= '<div class="leftFloat"';
                if (!empty($style)) {
                    $line .= ' style="' . $style . '"';
                }
                $line .= '>' . $extra[$c] . '</div>';

                $column++;
            }
        }

        return $line . "</div>\n";
    }

    /**
     * Sets the label on the tree line.
     *
     * @param string $node_id  The Node ID.
     *
     * @return string  The label for the tree line.
     */
    protected function _setLabel($node_id)
    {
        $n = $this->_nodes[$node_id];

        $output = '<span';
        if (!empty($n['onclick'])) {
            $output .= ' onclick="' . $n['onclick'] . '"';
        }
        $output .= '>';

        $label = $n['label'];
        if (!empty($n['url'])) {
            $target = '';
            if (!empty($n['target'])) {
                $target = ' target="' . $n['target'] . '"';
            } elseif ($target = $this->getOption('target')) {
                $target = ' target="' . $target . '"';
            }
            $output .= '<a' . (!empty($n['urlclass']) ? ' class="' . $n['urlclass'] . '"' : '') . ' href="' . $n['url'] . '"' . $target . '>' . $label . '</a>';
        } else {
            $output .= $label;
        }

        return $output . '</span>';
    }

    /**
     * Sets the node toggle on the tree line.
     *
     * @param string $node_id  The Node ID.
     *
     * @return string  The node toggle for the tree line.
     */
    protected function _setNodeToggle($node_id)
    {
        $link_start = '';

        if (($this->_nodes[$node_id]['indent'] == 0) &&
            isset($this->_nodes[$node_id]['children'])) {
            /* Top level node with children. */
            $this->_dropline[0] = false;
            if ($this->_static) {
                return '';
            } elseif (!$this->getOption('lines', false, true)) {
                $img = $this->_images['blank'];
                $alt = '&nbsp;&nbsp;&nbsp;';
            } elseif ($this->_nodes[$node_id]['expanded']) {
                $img = $this->_images['minus_only'];
                $alt = '-';
            } else {
                $img = $this->_images['plus_only'];
                $alt = '+';
            }
            if (!$this->_static) {
                $url = Horde_Util::addParameter(Horde::selfUrl(), self::TOGGLE . $this->_instance, $node_id);
                $link_start = Horde::link($url);
            }
        } elseif (($this->_nodes[$node_id]['indent'] != 0) &&
            !isset($this->_nodes[$node_id]['children'])) {
            /* Node without children. */
            if ($this->_node_pos[$node_id]['pos'] < $this->_node_pos[$node_id]['count']) {
                /* Not last node. */
                if ($this->getOption('lines', false, true)) {
                    $img = $this->_images['join'];
                    $alt = '|-';
                } else {
                    $img = $this->_images['blank'];
                    $alt = '&nbsp;&nbsp;&nbsp;';
                }
                $this->_dropline[$this->_nodes[$node_id]['indent']] = true;
            } else {
                /* Last node. */
                if ($this->getOption('lines', false, true)) {
                    $img = $this->_images['join_bottom'];
                    $alt = '`-';
                } else {
                    $img = $this->_images['blank'];
                    $alt = '&nbsp;&nbsp;&nbsp;';
                }
                $this->_dropline[$this->_nodes[$node_id]['indent']] = false;
            }
        } elseif (isset($this->_nodes[$node_id]['children'])) {
            /* Node with children. */
            if ($this->_node_pos[$node_id]['pos'] < $this->_node_pos[$node_id]['count']) {
                /* Not last node. */
                if (!$this->getOption('lines', false, true)) {
                    $img = $this->_images['blank'];
                    $alt = '&nbsp;&nbsp;&nbsp;';
                } elseif ($this->_static) {
                    $img = $this->_images['join'];
                    $alt = '|-';
                } elseif ($this->_nodes[$node_id]['expanded']) {
                    $img = $this->_images['minus'];
                    $alt = '-';
                } else {
                    $img = $this->_images['plus'];
                    $alt = '+';
                }
                $this->_dropline[$this->_nodes[$node_id]['indent']] = true;
            } else {
                /* Last node. */
                if (!$this->getOption('lines', false, true)) {
                    $img = $this->_images['blank'];
                    $alt = '&nbsp;&nbsp;&nbsp;';
                } elseif ($this->_static) {
                    $img = $this->_images['join_bottom'];
                    $alt = '`-';
                } elseif ($this->_nodes[$node_id]['expanded']) {
                    $img = $this->_images['minus_bottom'];
                    $alt = '-';
                } else {
                    $img = $this->_images['plus_bottom'];
                    $alt = '+';
                }
                $this->_dropline[$this->_nodes[$node_id]['indent']] = false;
            }
            if (!$this->_static) {
                $url = Horde_Util::addParameter(Horde::selfUrl(), self::TOGGLE . $this->_instance, $node_id);
                $link_start = Horde::link($url);
            }
        } else {
            /* Top level node with no children. */
            if ($this->_static) {
                return '';
            }
            if ($this->getOption('lines', false, true)) {
                $img = $this->_images['null_only'];
                $alt = '&nbsp;&nbsp;';
            } else {
                $img = $this->_images['blank'];
                $alt = '&nbsp;&nbsp;&nbsp;';
            }
            $this->_dropline[0] = false;
        }

        $link_end = ($link_start) ? '</a>' : '';

        $img = $link_start . '<img src="' . $this->_img_dir . '/' . $img . '"'
            . (isset($alt) ? ' alt="' . $alt . '"' : '')
            . ' height="20" width="20" style="vertical-align:middle" border="0" />'
            . $link_end;

        return $img;
    }

    /**
     * Sets the icon for the node.
     *
     * @param string $node_id  The Node ID.
     *
     * @return string  The node icon for the tree line.
     */
    protected function _setNodeIcon($node_id)
    {
        $img_dir = isset($this->_nodes[$node_id]['icondir']) ? $this->_nodes[$node_id]['icondir'] : $this->_img_dir;
        if ($img_dir) {
            $img_dir .= '/';
        }

        if (isset($this->_nodes[$node_id]['icon'])) {
            if (empty($this->_nodes[$node_id]['icon'])) {
                return '';
            }
            /* Node has a user defined icon. */
            if (isset($this->_nodes[$node_id]['iconopen']) &&
                $this->_nodes[$node_id]['expanded']) {
                $img = $this->_nodes[$node_id]['iconopen'];
            } else {
                $img = $this->_nodes[$node_id]['icon'];
            }
        } else {
            /* Use standard icon set. */
            if (isset($this->_nodes[$node_id]['children'])) {
                /* Node with children. */
                $img = ($this->_nodes[$node_id]['expanded']) ? $this->_images['folder_open'] : $this->_images['folder'];
            } else {
                /* Leaf node (no children). */
                $img = $this->_images['leaf'];
            }
        }

        $imgtxt = '<img src="' . $img_dir . $img . '"';

        /* Does the node have user defined alt text? */
        if (isset($this->_nodes[$node_id]['iconalt'])) {
            $imgtxt .= ' alt="' . htmlspecialchars($this->_nodes[$node_id]['iconalt']) . '"';
        }

        return $imgtxt . ' />';
    }

}
