<?php
namespace ZExt\Html\DescriptionList;

class ListElement {
	
	/**
	 * Term tag's instance
	 *
	 * @var Term
	 */
	protected $term;
	
	/**
	 * Description tag's instance
	 *
	 * @var Description
	 */
	protected $description;
	
	/**
	 * Constructor
	 * 
	 * @param type $term      Term instance or definition
	 * @param type $desc      Description instance or definition
	 * @param type $termAttrs Attrs for the term definition
	 * @param type $descAttrs Attrs for the description definition
	 */
	public function __construct($term = null, $desc = null, $termAttrs = null, $descAttrs = null) {
		if ($term !== null) {
			if (! $term instanceof Term) {
				$this->term = new Term($term, $termAttrs);
			} else {
				$this->term = $term;
			}
		}
		
		if ($desc !== null) {
			if (! $desc instanceof Description) {
				$this->description = new Description($desc, $descAttrs);
			} else {
				$this->description = $desc;
			}
		}
	}
	
	/**
	 * Set the term
	 * 
	 * @param string | Term $term
	 */
	public function setTerm($term) {
		if (! $term instanceof Term) {
			$this->getTerm()->setHtml($term);
		}
		
		$this->term = $term;
	}
	
	/**
	 * Get the term
	 * 
	 * @return Term
	 */
	public function getTerm() {
		if ($this->term === null) {
			$this->term = new Term();
		}
		
		return $this->term;
	}
	
	/**
	 * Set the description
	 * 
	 * @param string | Description $desc
	 */
	public function setDescription($desc) {
		if (! $this->description instanceof Description) {
			$this->getDescription()->setHtml($html);
		}
		
		$this->description = $desc;
	}
	
	/**
	 * Get the description
	 * 
	 * @return Description
	 */
	public function getDescription() {
		if ($this->description === null) {
			$this->description = new Description();
		}
		
		return $this->description;
	}
	
	/**
	 * Render the element
	 * 
	 * @return string
	 */
	public function render() {
		return $this->getTerm()->render() . $this->getDescription()->render();
	}
	
	/**
	 * Render the element
	 * 
	 * @return string
	 */
	public function __toString() {
		return $this->render();
	}
	
}