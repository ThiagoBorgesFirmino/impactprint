<?php
/**
 * Template Management for PHP5
 *
 * The Template engine allows to keep the HTML code in some external files
 * which are completely free of PHP code. This way, it's possible keep logical
 * programmin (PHP code) away from visual structure (HTML or XML, CSS, etc).
 *
 * If you are familiar with PHP template concept, this class includes these
 * features: object support, auto-detect blocks, auto-clean children blocks,
 * warning when user call for a non-existent block, warning when a mal-formed
 * block is detected, warning when user sets a non existant variable, and other
 * minor features.
 *
 * @author Rael G.C. (rael.gc@gmail.com)
 * @version 1.92
 */
class Template {

	/**
	 * A list of existent document variables.
	 *
	 * @var       array
	 */
	private $vars = array();

	/**
	 * A hash with vars and values setted by the user.
	 *
	 * @var       array
	 */
	private $values = array();

	/**
	 * A hash of existent object properties variables in the document.
	 *
	 * @var       array
	 */
	private $properties = array();

	/**
	 * A hash of the object instances setted by the user.
	 *
	 * @var       array
	 */
	private $instances = array();

	/**
	 * A list of all automatic recognized blocks.
	 *
	 * @var       array
	 */
	private $blocks = array();

	/**
	 * A list of all blocks that contains at least a "child" block.
	 *
	 * @var       array
	 */
	private $parents = array();

	/**
	 * Describes the replace method for blocks. See the Template::setFile()
	 * method for more details.
	 *
	 * @var       boolean
	 */
	private $accurate;

	/**
	 * Regular expression to find var and block names.
	 * Only alfa-numeric chars and the underscore char are allowed.
	 *
	 * @var		string
	 */
	private static $REG_NAME = "([[:alnum:]]|_)+";

    private $filename;

	/**
	 * Creates a new template, using $filename as main file.
	 *
	 * When the parameter $accurate is true, blocks will be replaced perfectly
	 * (in the parse time), e.g., removing all \t (tab) characters, making the
	 * final document an accurate version. This will impact (a lot) the
	 * performance. Usefull for files using the &lt;pre&gt; or &lt;code&gt; tags.
	 *
	 * @param     string $filename		file path of the file to be loaded
	 * @param     booelan $accurate		true for accurate block parsing
	 */
	public function __construct($filename, $accurate = false){
		$this->accurate = $accurate;
        $this->filename = $filename;
		$this->loadfile(".", $filename);
	}

    public function reloadfile(){
        $this->blocks = array();
        $this->loadfile('.', $this->filename);
    }

	/**
	 * Put the content of $filename in the template variable identified by $varname
	 *
	 * @param     string $varname		existing template var
	 * @param     string $filename		file to be loaded
	 */
	public function addFile($varname, $filename){
		if(!$this->exists($varname)) throw new InvalidArgumentException("addFile: var $varname does not exist");
		$this->loadfile($varname, $filename);
	}

	/**
	 * Do not use. Properties setter method
	 *
	 * @param	string	$varname	template var name
	 * @param	mixed	$value		template var value
	 */
	public function __set($varname, $value){
		if(!$this->exists($varname)) throw new RuntimeException("var $varname does not exist");
		$stringValue = $value;
		if(is_object($value)){
			$this->instances[$varname] = $value;
			if(!array_key_exists($varname, $this->properties)) $this->properties[$varname] = array();
			if(method_exists($value, "__toString")) $stringValue = $value->__toString();
			else $stringValue = "Object";
		}
		$this->setValue($varname, $stringValue);
		return $value;
	}

	/**
	 * Do not use. Properties getter method.
	 *
	 * @param	string	$varname	template var name
	 */
	public function __get($varname){
		if (isset($this->values["{".$varname."}"])) return $this->values["{".$varname."}"];
		throw new RuntimeException("var $varname does not exist");
	}

	/**
	 * Check if a template var exists.
	 *
	 * This method returns true if the template var exists. Otherwise, false.
	 *
	 * @param	string	$varname	template var name
	 */
	public function exists($varname){
		return in_array($varname, $this->vars);
	}

	/**
	 * Loads a file identified by $filename.
	 *
	 * The file will be loaded and the file's contents will be assigned as the
	 * variable's value.
	 * Additionally, this method call Template::recognize() that identifies
	 * all blocks and variables automatically.
	 *
	 * @param     string $varname		contains the name of a variable to load
	 * @param     string $filename		file name to be loaded
	 *
	 * @return    void
	 */
	private function loadfile($varname, $filename) {
		if (!file_exists($filename)) throw new InvalidArgumentException("file $filename does not exist");
		// If it's PHP file, parse it
		if($this->isPHP($filename)){
			ob_start();
			require $filename;
			$str = ob_get_contents();
			$str = trim($str);
			ob_end_clean();
			$this->setValue($varname, $str);
		} else {
			// Reading file and hiding comments
			$str = preg_replace("/<!---.*?--->/smi", "", file_get_contents($filename));
			$str = trim($str);
			$this->setValue($varname, $str);
			$blocks = $this->recognize($str, $varname);
			if (empty($str)) throw new InvalidArgumentException("file $filename is empty");
			$this->createBlocks($blocks);
		}
	}

	/**
	 * Check if file is a .php
	 */
	private function isPHP($filename){
		foreach(array('.php', '.php5', '.cgi') as $php){
			if(0 == strcasecmp($php, substr($filename, strripos($filename, $php)))) return true;
		}
		return false;
	}

	/**
	 * Identify all blocks and variables automatically and return them.
	 *
	 * All variables and blocks are already identified at the moment when
	 * user calls Template::setFile(). This method calls Template::identifyVars()
	 * and Template::identifyBlocks() methods to do the job.
	 *
	 * @param     string	$content		file content
	 * @param     string	$varname		contains the variable name of the file
	 *
	 * @return    array		an array where the key is the block name and the value is an
	 * 						array with the children block names.
	 */
	private function recognize(&$content, $varname){
		$blocks = array();
		$queued_blocks = array();
		foreach (explode("\n", $content) as $line) {
			if (strpos($line, "{")!==false) $this->identifyVars($line);
			if (strpos($line, "<!--")!==false) $this->identifyBlocks($line, $varname, $queued_blocks, $blocks);
		}
		return $blocks;
	}

	/**
	 * Identify all user defined blocks automatically.
	 *
	 * @param     string $line				contains one line of the content file
	 * @param     string $varname			contains the filename variable identifier
	 * @param     string $queued_blocks		contains a list of the current queued blocks
	 * @param     string $blocks			contains a list of all identified blocks in the current file
	 *
	 * @return    void
	 */
	private function identifyBlocks(&$line, $varname, &$queued_blocks, &$blocks){
		$reg = "/<!--\s*BEGIN\s+(".self::$REG_NAME.")\s*-->/sm";
		preg_match($reg, $line, $m);
		if (1==preg_match($reg, $line, $m)){
			if (0==sizeof($queued_blocks)) $parent = $varname;
			else $parent = end($queued_blocks);
			if (!isset($blocks[$parent])){
				$blocks[$parent] = array();
			}
			$blocks[$parent][] = $m[1];
			$queued_blocks[] = $m[1];
		}
		$reg = "/<!--\s*END\s+(".self::$REG_NAME.")\s*-->/sm";
		if (1==preg_match($reg, $line)) array_pop($queued_blocks);
	}

	/**
	 * Identifies all variables defined in the document.
	 *
	 * @param     string $line				contains one line of the content file
	 */
	private function identifyVars(&$line){
		$r = preg_match_all("/{(".self::$REG_NAME.")((\-\>(".self::$REG_NAME."))*)?}/", $line, $m);
		if ($r){
			for($i=0; $i<$r; $i++){
				// Object var detected
				if($m[3][$i] && (!array_key_exists($m[1][$i], $this->properties) || !in_array($m[3][$i], $this->properties[$m[1][$i]]))){
					$this->properties[$m[1][$i]][] = $m[3][$i];
				}
				if(!in_array($m[1][$i], $this->vars)) $this->vars[] = $m[1][$i];
			}
		}
	}

	/**
	 * Create all identified blocks given by Template::identifyBlocks().
	 *
	 * @param     array $blocks		contains all identified block names
	 * @return    void
	 */
	private function createBlocks(&$blocks) {
		$this->parents = array_merge($this->parents, $blocks);
		foreach($blocks as $parent => $block){
			foreach($block as $chield){
				if(in_array($chield, $this->blocks)) throw new UnexpectedValueException("duplicated block: $chield");
				$this->blocks[] = $chield;
				$this->setBlock($parent, $chield);
			}
		}
	}

	/**
	 * A variable $parent may contain a variable block defined by:
	 * &lt;!-- BEGIN $varname --&gt; content &lt;!-- END $varname --&gt;.
	 *
	 * This method removes that block from $parent and replaces it with a variable
	 * reference named $block. The block is inserted into the varKeys and varValues
	 * hashes.
	 * Blocks may be nested.
	 *
	 * @param     string $parent	contains the name of the parent variable
	 * @param     string $block		contains the name of the block to be replaced
	 * @return    void
	 */
	private function setBlock($parent, $block) {
		$name = "B_".$block;
		$str = $this->getVar($parent);
		if($this->accurate){
			$str = str_replace("\r\n", "\n", $str);
			$reg = "/\t*<!--\s*BEGIN\s+$block\s+-->\n*(\s*.*?\n?)\t*<!--\s+END\s+$block\s*-->\n?/sm";
		}
		else $reg = "/<!--\s*BEGIN\s+$block\s+-->\s*(\s*.*?\s*)<!--\s+END\s+$block\s*-->\s*/sm";
		if(1!==preg_match($reg, $str, $m)) throw new UnexpectedValueException("mal-formed block $block");
		$this->setValue($name, '');
		$this->setValue($block, $m[1]);
		$this->setValue($parent, preg_replace($reg, "{".$name."}", $str));
	}

	/**
	 * Internal setValue() method.
	 *
	 * The main difference between this and Template::__set() method is this
	 * method cannot be called by the user, and can be called using variables or
	 * blocks as parameters.
	 *
	 * @param     string $varname		constains a varname
	 * @param     string $value        constains the new value for the variable
	 * @return    void
	 */
	private function setValue($varname, $value) {
		$this->values["{".$varname."}"] = trim($value);
	}

	/**
	 * Returns the value of the variable identified by $varname.
	 *
	 * @param     string	$varname	the name of the variable to get the value of
	 * @return    string	the value of the variable passed as argument
	 */
	
	
	private function getVar($varname){		
		return trim($this->values['{'.$varname.'}']);
	}
	
	public function getValues(){
		return $this->values;
	}
	public function verifcarVariavel($varname) {
		if(isset($this->values['{'.$varname.'}'])){
			return true;
		}
		return false;
	}
	public function verifcarVariavelMiolo($varname) {
		if(preg_match('/{'.$varname.'}/', $this->retornaVar('miolo'), $matches, PREG_OFFSET_CAPTURE)){
			return true;
		}
		return false;
	}
	
	public function retornaVar($varname){
		return $this->getVar($varname);
	}

	/**
	 * Clear the value of a variable.
	 *
 	 * Alias for $this->setValue($varname, "");
	 *
	 * @param     string $varname	var name to be cleaned
	 * @return    void
	 */
	public function clear($varname){
		$this->setValue($varname,"");
	}

	/**
	 * Fill in all the variables contained within the variable named
	 * $varname. The resulting value is returned as the function result and the
	 * original value of the variable varname is not changed. The resulting string
	 * is not "finished", that is, the unresolved variable name policy has not been
	 * applied yet.
	 *
	 * @param     string 	$varname      the name of the variable within which variables are to be substituted
	 * @return    string	the value of the variable $varname with all variables substituted.
	 */
	private function subst($varname) {
		$s = $this->getVar($varname);
		
		// Common variables replacement
		$s = str_replace(array_keys($this->values), $this->values, $s);
		//butil::printr($s);
		
		
		// Object variables replacement
		foreach($this->instances as $var => $instance){
			foreach($this->properties[$var] as $properties){
				if(false!==strpos($s, "{".$var.$properties."}")){
					$pointer = $instance;
					$property = explode("->", $properties);
					for($i = 1; $i < sizeof($property); $i++){
						if(!is_null($pointer)){
						
							
						
							$obj = strtolower(str_replace('','', $property[$i]));
							$objMethod = (str_replace('_', '', $property[$i]));
							
							// butil::printr("get$objMethod");
							
							// Property acessor
							if(property_exists($pointer, $obj)){
								$pointer = $pointer->$obj;
							// Non boolean accessor
							} elseif(method_exists($pointer, "get$objMethod")){
								$pointer = $pointer->{"get$objMethod"}();
							// Boolean accessor
							} elseif(method_exists($pointer, "is$obj")){
								$pointer = $pointer->{"is$obj"}();
							// Magic __get accessor
							} elseif(method_exists($pointer, "__get")){
								$pointer = $pointer->__get($property[$i]);
							// Accessor dot not exists: throw Exception
							} else {
								$className = $property[$i-1] ? $property[$i-1] : get_class($instance);
								$class = is_null($pointer) ? "NULL" : get_class($pointer);
								throw new BadMethodCallException("no accessor method in class ".$class." for ".$className."->".$property[$i]);
							}
						}
					}
					// Checking if final value is an object
					if(is_object($pointer)){
						if(method_exists($pointer, "__toString")){
							$pointer = $pointer->__toString();
						} else {
							$pointer = "Object";
						}
					}
					// Replace
					$s = str_replace("{".$var.$properties."}", $pointer, $s);
				}
			}
		}
		return trim($s);}

	/**
	 * Clear all child blocks of a given block.
	 *
	 * @param     string $block	a block with chield blocks.
	 */
	private function clearBlocks($block) {
		if (isset($this->parents[$block])){
			$chields = $this->parents[$block];
			foreach($chields as $chield){
				$this->clear("B_".$chield);
			}
		}
	}

	/**
	 * Show a block.
	 *
	 * This method must be called when a block must be showed.
	 * Otherwise, the block will not appear in the resultant
	 * content.
	 *
	 * If the parameter $append is true, the block
	 * content will be appended with the previous content.
	 *
	 * @param     string $block		the block name to be parsed
	 * @param     boolean $append	true if the content must be appended
	 */
	public function block($block, $append = true) {
		if(!in_array($block, $this->blocks)) throw new InvalidArgumentException("block $block does not exist");
		if ($append) $this->setValue("B_".$block, $this->getVar("B_".$block) . $this->subst($block));
		else $this->setValue("B_".$block, $this->subst($block));
		$this->clearBlocks($block);
	}

	/**
	* Returns the final content
	*
	* @return    string
	*/
	public function parse(){
		// After subst, remove empty vars
		return trim(preg_replace("/{(".self::$REG_NAME.")((\-\>(".self::$REG_NAME."))*)?}/","", $this->subst(".")));
	}

	/**
	 * Print the final content.
	 */
	public function show() {
		echo trim($this->parse());
	}










	// Compatibility
	public function parseBlock($block, $append = true) {
		$this->block($block, $append);
	}
	
	public function getContent() {
		return trim($this->parse());
	}

}
?>