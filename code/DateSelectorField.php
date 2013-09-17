<?php

/**
 * @package datedropdownselectorfield
 */
class DateSelectorField extends CompositeField {

	protected $modifier;

	public function __construct($name, $title = null, $value = null, $modifier = null) {
		$this->name = $name;

		$this->setTitle($title);
		$this->modifier = $modifier;
		$dayArray = array(
			0 => _t('DateSelectorField.DAY','Day')
		);
		for($i = 1; $i < 32; $i++) {
			$dayArray[$i] = $i;
		}
		$monthArray = array(
			0 => _t('DateSelectorField.MONTH','Month'),
			1 => _t('DateSelectorField.JAN','Jan'),
			2 => _t('DateSelectorField.FEB','Feb'),
			3 => _t('DateSelectorField.MARCH','March'),
			4 => _t('DateSelectorField.APR','Apr'),
			5 => _t('DateSelectorField.MAY','May'),
			6 => _t('DateSelectorField.JUNE','June'),
			7 => _t('DateSelectorField.JULY','July'),
			8 => _t('DateSelectorField.AUG','Aug'),
			9 => _t('DateSelectorField.SEPT','Sept'),
			10 => _t('DateSelectorField.OCT','Oct'),
			11 => _t('DateSelectorField.NOV','Nov'),
			12 => _t('DateSelectorField.DEC','Dec')
		);
		$now = new DateTime();
		$startYear = $now->format('Y');
		$endYear = $startYear - 105;
		$yearArray = array(
			0 =>  _t('DateSelectorField.YEAR','Year')
		);
		
		for($i = $startYear; $i >= $endYear; $i--) {
			$yearArray[$i] = $i;
		}
		
		$fields = array(
			new DropdownField($this->name.'[' . $this->modifier . 'Day]', '', $dayArray),
			new DropdownField($this->name.'[' . $this->modifier . 'Month]', '', $monthArray),
			new DropdownField($this->name.'[' . $this->modifier . 'Year]', '', $yearArray)
		);

		foreach($fields as $field) {
			$field->addExtraClass('dateselectorfield');
		}

		if($title) {
			array_unshift($fields, $header = new HeaderField($this->name.'['. $this->modifier .'Title]', $title . ' ' . $this->modifier, 4));

			$header->setAllowHTML(true);
		}
	
		parent::__construct($fields);
		$this->setValue($value);
	}

	/**
	 * Returns the fields nested inside another DIV
	 *
	 * @param array
	 * @return html
	 */
	public function FieldHolder($properties = array()) {
		$idAtt = isset($this->id) ? " id=\"{$this->id}\"" : '';
		$className = ($this->columnCount) ? "field CompositeField {$this->extraClass()} multicolumn" : "field CompositeField {$this->extraClass()}";
		$content = "<div class=\"$className\"$idAtt>\n";
		
		foreach($this->getChildren() as $subfield) {
			if($this->columnCount) {
				$className = "column{$this->columnCount}";
				if(!next($fs)) $className .= " lastcolumn";
				$content .= "\n<div class=\"{$className}\">\n" . $subfield->FieldHolder() . "\n</div>\n";
			} else if($subfield){
				$content .= "\n" . $subfield->FieldHolder() . "\n";
			}
		}
		$message = $this->Message();
		$type = $this->MessageType();

		$content .= (!empty($message)) ? "<span class=\"message $type\">$message</span>" : "";

		$content .= "</div>\n";
				
		return $content;
	}


	public function hasData() {
		return true;
	}

	public function isComposite() {
		return false;
	}

	public function getValue() {
		return $this->value;
	}

	public function setValue($value) {
		if (is_string($value)) {
			$value = new DateTime($value);
		}
		$d = null;
		$m = null;
		$y = null;
		if ($value instanceof DateTime) {
			$d = $value->format('j');
			$m = $value->format('n');
			$y = $value->format('Y');
		} else if (is_array($value)) {
			if (isset($value[$this->modifier . 'Day'])) {
				$d = $value[$this->modifier . 'Day'];
			}
			if (isset($value[$this->modifier . 'Month'])) {
				$m = $value[$this->modifier . 'Month'];
			}
			if (isset($value[$this->modifier . 'Year'])) {
				$y = $value[$this->modifier . 'Year'];
			}
		}
		if ($d && $m && $y) {
			foreach($this->getChildren() as $child) {
				switch($child->Name()) {
					case $this->name.'[' . $this->modifier . 'Day]':
						$child->setValue($d);
						break;
					case $this->name.'[' . $this->modifier . 'Month]':
						$child->setValue($m);
						break;
					case $this->name.'[' . $this->modifier . 'Year]':
						$child->setValue($y);
						break;
				}
			}
			if ($d < 10) {
				$d = '0'.$d;
			}
			if ($m < 10) {
				$m = '0'.$m;
			}
			$this->value = $y . '-' . $m . '-' . $d;
		}

		return $this;
	}

	function validate($i = null) {
		$children = $this->getChildren();
		$value = true;

		foreach($children as $child) {
			if(get_class($child) == "DropdownField") {
				if(!($value = $child->Value()) || $value == '') $value = false;
			}
		}

		return $value;
	}
}