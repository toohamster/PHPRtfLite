<?php
/* 
    PHPRtfLite
    Copyright 2007-2008 Denis Slaveckij <info@phprtf.com>
    Copyright 2010 Steffen Zeidler <sigma_z@web.de>

    This file is part of PHPRtfLite.

    PHPRtfLite is free software: you can redistribute it and/or modify
    it under the terms of the GNU Lesser General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    PHPRtfLite is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public License
    along with PHPRtfLite.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * Class for creating tables for rtf documents.
 * @version     1.1.0
 * @author      Denis Slaveckij <info@phprtf.com>
 * @author      Steffen Zeidler <sigma_z@web.de>
 * @copyright   2007-2008 Denis Slaveckij, 2010 Steffen Zeidler
 * @package     PHPRtfLite
 * @subpackage  PHPRtfLite_Table
 */
class PHPRtfLite_Table
{

    /**
     * constants table alignment
     */
    const ALIGN_LEFT    = 'left';
    const ALIGN_CENTER  = 'center';
    const ALIGN_RIGHT   = 'right';

    /**
     * @var PHPRtfLite_Container
     */
    protected $_container;

    /**
     * array of PHPRtfLite_Table_Row instances
     * @var array
     */
    protected $_rows;

    /**
     * array of PHPRtfLite_Table_Column instances
     * @var array
     */
    protected $_columns;

    /**
     * @var string
     */
    protected $_alignment;

    /**
     * @var boolean
     */
    protected $_preventPageBreak = false;

    /**
     * @var boolean
     */
    protected $_firstRowIsHeader = false;

    /**
     * @var boolean
     */
    protected $_repeatFirstRowHeader = false;

    /**
     * @var integer
     */
    protected $_leftPosition = 0;

    /**
     * nest depth
     * 0: main document
     * 1: table cell
     * 2: nested table cell
     * 3: double nested table cell
     * 4: three times nested table cell
     * .. and so on ..
     * @var integer
     */
    protected $_nestDepth = 1;

    
    /**
     * Constructor
     * @param PHPRtfLite_Container
     * @param string
     */
    public function __construct(PHPRtfLite_Container $container, $alignment = self::ALIGN_LEFT, $nestDepth = 1)
    {
        $this->_container = $container;
        $this->_alignment = $alignment;
        $this->_nestDepth = $nestDepth;
    }

    /**
     * gets nested depth
     * 
     * @return integer
     */
    public function getNestDepth()
    {
        return $this->_nestDepth;
    }

    /**
     * checks, if table is a nested table
     *
     * @return boolean
     */
    public function isNestedTable()
    {
        return $this->_nestDepth > 1;
    }


    /**
     * Gets rtf container instance
     *
     * @return PHPRtfLite_Container
     */
    public function getContainer()
    {
        return $this->_container;
    }

    /**
     * gets rtf instance
     * @return PHPRtfLite
     */
    public function getRtf()
    {
        return $this->_container->getRtf();
    }

    /**
     * Sets that table won't be splited by a page break. By default page break splits table.
     */
    public function setPreventPageBreak()
    {
        $this->_preventPageBreak = true;
    }

    /**
     * Returns true, if a table should not be splited by a page break
     *
     * @return boolean
     */
    public function isPreventPageBreak()
    {
        return $this->_preventPageBreak;
    }

    /**
     * Sets left position of table.
     * @param   float $leftPosition left position of table.
     */
    public function setLeftPosition($leftPosition)
    {
        $this->_leftPosition = $leftPosition;
    }

    /**
     * Gets left position of table.
     * 
     * @return  float
     */
    public function getLeftPosition()
    {
        return $this->_leftPosition;
    }

    /**
     * Sets first row as header row. First row will be repeated at the top of each page.
     */
    public function setFirstRowAsHeader()
    {
        $this->_firstRowIsHeader = true;
    }

    /**
     * Returns true, if first row should be used as header. First row will be repeated at the top of each page.
     * 
     * @return boolean
     */
    public function isFirstRowHeader()
    {
        return $this->_firstRowIsHeader;
    }

    /**
     * gets alignment
     * 
     * @return string
     */
    public function getAlignment()
    {
        return $this->_alignment;
    }

    /**
     * Adds rows
     * @param   integer $rowCnt
     * @param   float   $height row height. When null, the height is sufficient for all the text in the line;
     *   when positive, the height is guaranteed to be at least the specified height; when negative,
     *   the absolute value of the height is used, regardless of the height of the text in the line.
     */
    public function addRows($rowCnt, $height = null)
    {
        for ($i = 0; $i < $rowCnt; $i++) {
            $this->addRow($height);
        }
    }

    /**
     * Adds list of rows to a table.
     * @param array $heights array of heights for each row to add. When height is null,
     *   the height is sufficient for all the text in the line; when positive,
     *   the height is guaranteed to be at least the specified height; when negative,
     *   the absolute value of the height is used, regardless of the height of
     *   the text in the line.
     */
    public function addRowList($heights)
    {
        foreach ($heights as $height) {
            $this->addRow($height);
        }
    }

    /**
     * Adds row
     *
     * @param   float   $height row height. When 0, the height is sufficient for all the text in the line;
     *   when positive, the height is guaranteed to be at least the specified height; when negative,
     *   the absolute value of the height is used, regardless of the height of the text in the line.
     *
     * @return  PHPRtfLite_Table_Row
     */
    public function addRow($height = null)
    {
        $row = new PHPRtfLite_Table_Row($this, $this->getRowsCount() + 1, $height);
        $this->_rows[] = $row;

        return $row;
    }

    /**
     * Gets row instance
     * @param  integer $rowIndex
     *
     * @return PHPRtfLite_Table_Row
     * @throws PHPRtfLite_Exception, if rowIndex is not valid
     */
    public function getRow($rowIndex)
    {
        if (isset($this->_rows[$rowIndex - 1])) {
            return $this->_rows[$rowIndex - 1];
        }

        throw new PHPRtfLite_Exception('Invalid row index for table: ' . $rowIndex);
    }

    /**
     * Adds column
     *
     * @param   float   $width column width
     *
     * @return  PHPRtfLite_Table_Column
     */
    public function addColumn($width)
    {
        $column = new PHPRtfLite_Table_Column($width);
        $this->_columns[] = $column;
        
        return $column;
    }

    /**
     * Gets column
     * @param  integer $colIndex
     *
     * @return PHPRtfLite_Table_Column
     * @throws PHPRtfLite_Exception, if colIndex is not valid
     */
    public function getColumn($colIndex)
    {
        if (isset($this->_columns[$colIndex - 1])) {
            return $this->_columns[$colIndex - 1];
        }

        throw new PHPRtfLite_Exception('Invalid column index for table: ' . $colIndex);
    }

    /**
     * Adds list of columns
     * @param  array array of column widths.
     */
    public function addColumnsList($columnWidths)
    {
        foreach ($columnWidths as $columnWidth) {
            $this->addColumn($columnWidth);
        }
    }

    /**
     * Gets the instance of cell.
     * @param  integer $rowIndex
     * @param  integer $columnIndex
     *
     * @return PHPRtfLite_Table_Cell
     * @throws PHPRtfLite_Exception, if index for row or column is not valid
     */
    public function getCell($rowIndex, $columnIndex)
    {
        if ($this->checkIfCellExists($rowIndex, $columnIndex)) {
            return $this->getRow($rowIndex)->getCellByIndex($columnIndex);
        }

        throw new PHPRtfLite_Exception('Wrong index for cell! You gave me: (row:' . $rowIndex . ', column:' . $columnIndex . ')');
    }

    /**
     * Writes text to cell.
     *
     * @param   integer                 $rowIndex           row index of cell
     * @param   integer                 $columnIndex        column index of cell
     * @param   string                  $text               Text. Also you can use html style tags. @see PHPRtfLite_Container#writeText()
     * @param   PHPRtfLite_Font         $font               Font of text
     * @param   PHPRtfLite_ParFormat    $parFormat          Paragraph format
     * @param   boolean                 $convertTagsToRtf   If false, then html style tags are not replaced with rtf code.
     */
    public function writeToCell($rowIndex,
                                $columnIndex,
                                $text,
                                PHPRtfLite_Font $font = null,
                                PHPRtfLite_ParFormat $parFormat = null,
                                $convertTagsToRtf = true)
    {
        if ($this->checkIfCellExists($rowIndex, $columnIndex)) {
            $this->getCell($rowIndex, $columnIndex)->writeText($text, $font, $parFormat, $convertTagsToRtf);
        }
    }

    /**
     * Adds image to cell.
     *
     * @param   integer                 $rowIndex       row index of cell
     * @param   integer                 $columnIndex    column index of cell
     * @param   string                  $file           image file.
     * @param   PHPRtfLite_ParFormat    $parFormat      paragraph format
     * @param   float                   $width          if null image is displayed by it's original height.
     * @param   float                   $height         if null image is displayed by it's original width. If boths parameters are null, image is displayed as it is.
     *
     * @return PHPRtfLite_Image
     */
    public function addImageToCell($rowIndex,
                                   $columnIndex,
                                   $file,
                                   PHPRtfLite_ParFormat $parFormat = null,
                                   $width = null,
                                   $height = null)
    {
        if ($this->checkIfCellExists($rowIndex, $columnIndex)) {
            $this->getCell($rowIndex, $columnIndex)->addImage($file, $parFormat, $width, $height);
        }
    }

    /**
     * corrects cell range to be valid
     *
     * @param integer $startRow
     * @param integer $startColumn
     * @param integer $endRow
     * @param integer $endColumn
     * @return array
     */
    private static function getValidCellRange($startRow, $startColumn, $endRow, $endColumn)
    {
        if ($endRow === null) {
            $endRow = $startRow;
        }
        elseif ($startRow > $endRow) {
            $temp = $startRow;
            $startRow = $endRow;
            $endRow = $temp;
        }

        if ($endColumn === null) {
            $endColumn = $startColumn;
        }
        elseif ($startColumn > $endColumn) {
            $temp = $startColumn;
            $startColumn = $endColumn;
            $endColumn = $temp;
        }

        return array($startRow, $startColumn, $endRow, $endColumn);
    }

    private function getCellsByCellRange($startRow, $startColumn, $endRow, $endColumn)
    {
        $cells = array();
        for ($row = $startRow; $row <= $endRow; $row++) {
            for ($column = $startColumn; $column <= $endColumn; $column++) {
                $cells[] = $this->getCell($row, $column);
            }
        }
        return $cells;
    }

    /**
     * Sets vertical alignment to cells of a given cell range
     * @param   string  $verticalAlignment Vertical alignment of cell (default top). Represented by PHPRtfLite_Container::VERTICAL_ALIGN_*<br>
     *   Possible values:<br>
     *     PHPRtfLite_Container::VERTICAL_ALIGN_TOP     => 'top'    - top alignment;<br>
     *     PHPRtfLite_Container::VERTICAL_ALIGN_CENTER  => 'center' - center alignment;<br>
     *     PHPRtfLite_Container::VERTICAL_ALIGN_BOTTOM  => 'bottom' - bottom alignment.
     * 
     * @param   integer $startRow       start row
     * @param   integer $startColumn    start column
     * @param   integer $endRow         end row, if null, then vertical alignment is set only to the row range.
     * @param   integer $endColumn      end column, if null, then vertical alignment is set just to the column range.
     */
    public function setVerticalAlignmentForCellRange($verticalAlignment, $startRow, $startColumn, $endRow = null, $endColumn = null)
    {
        list($startRow, $startColumn, $endRow, $endColumn)
                = PHPRtfLite_Table::getValidCellRange($startRow, $startColumn, $endRow, $endColumn);

        if ($this->checkIfCellExists($startRow, $startColumn)
            && $this->checkIfCellExists($endRow, $endColumn))
        {
            $cells = $this->getCellsByCellRange($startRow, $startColumn, $endRow, $endColumn);
            foreach ($cells as $cell) {
                $cell->setVerticalAlignment($verticalAlignment);
            }
        }
    }

    /**
     * Sets alignments to empty cells of a given cell range.
     *
     * @param   string  $alignment      alignment of cell. The method PHPRtfLite_Table_Cell->writeToCell() overrides it with PHPRtfLite_ParFormat alignment.<br>
     *   Alignment is represented by class constants PHPRtfLite_Container::TEXT_ALIGN_*<br>
     *   Possible values:<br>
     *     PHPRtfLite_Container::TEXT_ALIGN_LEFT    => 'left'   - left alignment<br>
     *     PHPRtfLite_Container::TEXT_ALIGN_RIGHT   => 'right'  - right alignment<br>
     *     PHPRtfLite_Container::TEXT_ALIGN_CENTER  => 'center' - center alignment<br>
     *     PHPRtfLite_Container::TEXT_ALIGN_JUSTIFY => 'justify' - justify alignment
     * 
     * @param   integer $startRow       start row
     * @param   integer $startColumn    start column
     * @param   integer $endRow         end row, if null, then text alignment is set only to the row range.
     * @param   integer $endColumn      end column, if null, then text alignment is set just to the column range.
     */
    public function setTextAlignmentForCellRange($alignment, $startRow, $startColumn, $endRow = null, $endColumn = null)
    {
        list($startRow, $startColumn, $endRow, $endColumn)
                = PHPRtfLite_Table::getValidCellRange($startRow, $startColumn, $endRow, $endColumn);

        if ($this->checkIfCellExists($startRow, $startColumn)
            && $this->checkIfCellExists($endRow, $endColumn)) 
        {
            $cells = $this->getCellsByCellRange($startRow, $startColumn, $endRow, $endColumn);
            foreach ($cells as $cell) {
                $cell->setTextAlignment($alignment);
            }
        }
    }

    /**
     * Sets font to empty cells of a given cell range.
     *
     * @param   PHPRtfLite_Font $font           font for empty cells. The method PHPRtfLite_Table_Cell->writeToCell() overrides it with another PHPRtfLite_Font.
     * @param   integer         $startRow       start row
     * @param   integer         $startColumn    start column
     * @param   integer         $endRow         end row, if null, then font is set only to the row range.
     * @param   integer         $endColumn      end column, if null, then font is set just to the column range.
     */
    public function setFontForCellRange(PHPRtfLite_Font $font, $startRow, $startColumn, $endRow = null, $endColumn = null)
    {
        list($startRow, $startColumn, $endRow, $endColumn)
                = PHPRtfLite_Table::getValidCellRange($startRow, $startColumn, $endRow, $endColumn);

        if ($this->checkIfCellExists($startRow, $startColumn)
            && $this->checkIfCellExists($endRow, $endColumn))
        {
            $cells = $this->getCellsByCellRange($startRow, $startColumn, $endRow, $endColumn);
            foreach ($cells as $cell) {
                $cell->setFont($font);
            }
        }
    }

    /**
     * Rotates cells of a given cell range.
     * 
     * @param   string  $rotateTo       direction of rotation<br>
     *   Possible values (represented by PHPRtfLite_Table_Cell::ROTATE_*):<br>
     *     PHPRtfLite_Table_Cell::ROTATE_RIGHT  => 'right'<br>
     *     PHPRtfLite_Table_Cell::ROTATE_LEFT   => 'left'
     *
     * @param   integer $startRow       start row
     * @param   integer $startColumn    start column
     * @param   integer $endRow         end row, if null, then rotation is set only to the row range.
     * @param   integer $endColumn      end column, if null, then rotation is set just to the column range.
     */
    public function rotateCellRange($rotateTo, $startRow, $startColumn, $endRow = null, $endColumn = null)
    {
        list($startRow, $startColumn, $endRow, $endColumn)
                = PHPRtfLite_Table::getValidCellRange($startRow, $startColumn, $endRow, $endColumn);

        if ($this->checkIfCellExists($startRow, $startColumn)
            && $this->checkIfCellExists($endRow, $endColumn)) 
        {
            $cells = $this->getCellsByCellRange($startRow, $startColumn, $endRow, $endColumn);
            foreach ($cells as $cell) {
                $cell->rotateTo($rotateTo);
            }
        }
    }

    /**
     * Sets background color of cells of a given cell range.
     *
     * @param   string  $backgroundColor    background color
     * @param   integer $startRow           start row
     * @param   integer $startColumn        start column
     * @param   integer $endRow             end row, if null, then rotation is set only to the row range.
     * @param   integer $endColumn          end column, if null, then rotation is set just to the column range.
     */
    public function setBackgroundForCellRange($backgroundColor,
                                              $startRow,
                                              $startColumn,
                                              $endRow = null,
                                              $endColumn = null)
    {
        list($startRow, $startColumn, $endRow, $endColumn)
                = PHPRtfLite_Table::getValidCellRange($startRow, $startColumn, $endRow, $endColumn);

        if ($this->checkIfCellExists($startRow, $startColumn)
            && $this->checkIfCellExists($endRow, $endColumn))
        {
            $cells = $this->getCellsByCellRange($startRow, $startColumn, $endRow, $endColumn);
            foreach ($cells as $cell) {
                $cell->setBackgroundColor($backgroundColor);
            }
        }
    }

    /**
     * Sets border to cells of a given cell range.
     * 
     * @param   PHPRtfLite_Border   $border         border
     * @param   integer             $startRow       start row
     * @param   integer             $startColumn    start column
     * @param   integer             $endRow         end row, if null, then border is set only to the row range.
     * @param   integer             $endColumn      end column, if null, then border is set just to the column range.
     */
    public function setBorderForCellRange(PHPRtfLite_Border $border,
                                          $startRow, $startColumn,
                                          $endRow = null, $endColumn = null)
    {
        list($startRow, $startColumn, $endRow, $endColumn)
                = PHPRtfLite_Table::getValidCellRange($startRow, $startColumn, $endRow, $endColumn);

        if ($this->checkIfCellExists($startRow, $startColumn)
            && $this->checkIfCellExists($endRow, $endColumn))
        {
            $cells = $this->getCellsByCellRange($startRow, $startColumn, $endRow, $endColumn);
            foreach ($cells as $cell) {
                $cell->setBorder(clone($border));
            }
        }
    }

    /**
     * @deprecated use setBorderForCellRange() instead
     * @see PHPRtfLite/PHPRtfLite_Table#setBorderForCellRange()
     *
     * Sets borders of cells.
     *
     * @param   PHPRtfLite_Border_Format    $borderFormat   border format
     * @param   integer                     $startRow       start row
     * @param   integer                     $startColumn    start column
     * @param   integer                     $endRow         end row, if null, then border is set only to the row range.
     * @param   integer                     $endColumn      end column, if null, then border is set just to the column range.
     * @param   boolean                     $left           if false, left border is not set (default true)
     * @param   boolean                     $top            if false, top border is not set (default true)
     * @param   boolean                     $right          if false, right border is not set (default true)
     * @param   boolean                     $bottom         if false, bottom border is not set (default true)
     */
    public function setBordersForCellRange(PHPRtfLite_Border_Format $borderFormat,
                                           $startRow,
                                           $startColumn,
                                           $endRow = null,
                                           $endColumn = null,
                                           $left = true,
                                           $top = true,
                                           $right = true,
                                           $bottom = true)
    {
        $border = new PHPRtfLite_Border($this->getRtf());
        $border->setBorders($borderFormat, $left, $top, $right, $bottom);

        $this->setBorderForCellRange($border, $startRow, $startColumn, $endRow, $endColumn);
    }

    /**
     * Merges cells of a given cell range.
     *
     * @param   integer $startRow       start row
     * @param   integer $startColumn    start column
     * @param   integer $endRow         end row
     * @param   integer $endColumn      end column
     *
     * @TODO source code commentation
     */
    public function mergeCellRange($startRow, $startColumn, $endRow, $endColumn)
    {
        list($startRow, $startColumn, $endRow, $endColumn)
                = PHPRtfLite_Table::getValidCellRange($startRow, $startColumn, $endRow, $endColumn);

        if ($startRow == $endRow && $startColumn == $endColumn) {
            return;
        }

        if (!$this->checkIfCellExists($endRow, $endColumn)) {
            return;
        }

        for ($j = $startRow; $j <= $endRow; $j++) {
            $start = $startColumn;
            $cell = $this->getCell($j, $start);
            
            while ($cell->isHorizontalMerged()) {
                $start--;
                $cell = $this->getCell($j, $start);
            }

            $end = $endColumn;

            $cell = $this->getCell($j, $end);
            while ($cell->isHorizontalMerged()) {
                $end++;
                $cell = $this->getCell($j, $end + 1);
            }

            $width = 0;

            for ($i = $start; $i <= $end; $i++) {
                $cell = $this->getCell($j, $i);
                $cell->setVerticalMerged(true);
  
                $width += $this->getColumn($i)->getWidth();

                if ($i != $start) {
                    $cell->setHorizontalMerged(true);
                    $cell->setWidth(null);
                }
            }

            $this->getCell($j, $start)->setWidth($width);
        }
    }

    /**
     * Gets table rows
     * 
     * @return array instances of PHPRtfLite_Table_Row
     */
    public function getRows()
    {
        return $this->_rows;
    }

    /**
     * Gets number of rows in table
     *
     * @return integer
     */
    public function getRowsCount()
    {
        return count($this->_rows);
    }

    /**
     * Gets table columns
     *
     * @return array instances of PHPRtfLite_Table_Column
     */
    public function getColumns()
    {
        return $this->_columns;
    }

    /**
     * Gets number of columns in table
     *
     * @return integer
     */
    public function getColumnsCount()
    {
        return count($this->_columns);
    }

    /**
     * Returns true, if column index is valid
     *
     * @param   integer $colIndex
     * @return  boolean
     */
    public function checkColumnIndex($colIndex)
    {
        return isset($this->_columns[$colIndex - 1]);
    }

    /**
     * Returns true, if row index is valid
     *
     * @param   integer $rowIndex
     * @return  boolean
     */
    public function checkRowIndex($rowIndex)
    {
        return isset($this->_rows[$rowIndex - 1]);
    }


    /**
     * Returns true, if rowIndex and columnIndex do exists in table
     * 
     * @param  integer $rowIndex
     * @param  integer $columnIndex
     *
     * @return boolean
     */
    public function checkIfCellExists($rowIndex, $columnIndex)
    {
        return ($this->checkRowIndex($rowIndex) && $this->checkColumnIndex($columnIndex));
    }


    /**
     * Gets rtf code for table
     *
     * @return string rtf code
     */
    public function render()
    {
        if (empty($this->_rows) || empty($this->_columns)) {
            return;
        }

        $stream = $this->getRtf()->getStream();
        $stream->write('\trowd' . "\r\n");

        foreach ($this->_rows as $row) {
            $this->renderRowCells($row);
            $stream->write("\r\n" . '{');
            $this->renderRowDefinition($row);
            $stream->write('\row}' . "\r\n");
        }

        $stream->write('\pard\itap0' . "\r\n");
    }


    /**
     * renders row definition
     *
     * @param PHPRtfLite_Table_Row $row
     */
    public function renderRowDefinition(PHPRtfLite_Table_Row $row)
    {
        $rowIndex = $row->getRowIndex();
        $stream = $this->getRtf()->getStream();
        $stream->write('\trowd');

        if ($this->_alignment) {
            switch ($this->_alignment) {
                case self::ALIGN_CENTER:
                    $stream->write('\trqc');
                    break;

                case self::ALIGN_RIGHT:
                    $stream->write('\trqr');
                    break;

                default:
                    $stream->write('\trql');
                    break;
            }
        }

        $rowHeight = $row->getHeight();
        if ($rowHeight) {
            $stream->write('\trrh' . round($rowHeight * PHPRtfLite::TWIPS_IN_CM));
        }

        if ($this->isPreventPageBreak()) {
            $stream->write('\trkeep ');
        }

        if ($this->isFirstRowHeader() && $rowIndex == 1) {
            $stream->write('\trhdr ');
        }

        if ($this->getLeftPosition() != '') {
            $stream->write('\trleft' . round($this->getLeftPosition() * PHPRtfLite::TWIPS_IN_CM) . ' ');
        }

        $width = 0;
        foreach ($this->getColumns() as $columnIndex => $column) {
            $cell = $this->getCell($rowIndex, $columnIndex + 1);

            // render cell definition
            if (!$cell->isHorizontalMerged()) {
                $cell->renderDefinition();

                // cell width
                $width += round($cell->getWidth() * PHPRtfLite::TWIPS_IN_CM);
                $stream->write('\cellx' . $width);
            }
        }
    }


    /**
     * renders row cells
     *
     * @param PHPRtfLite_Table_Row $row
     */
    protected function renderRowCells(PHPRtfLite_Table_Row $row)
    {
        $rowIndex = $row->getRowIndex();
        $stream = $this->getRtf()->getStream();

        foreach ($this->getColumns() as $columnIndex => $column) {
            $cell = $this->getCell($rowIndex, $columnIndex + 1);
            if (!$cell->isHorizontalMerged()) {
                $cell->render();
            }
        }
    }

}