<?php
/**
 * 
 * 
 * @using:
 * 		const: CFG_SITE_NAME, FS_ROOT, WWW_ROOT, AJAX_MODE
 * 
 */
class Layout{
	
	protected $_tplPath = null;
	
	protected $_htmlTitle = '';
	protected $_htmlLinkTags = array();
	protected $_breadcrumbs = array();
	protected $_isAutoBreadcrumbsAdded = FALSE;
	protected $_htmlContent = '';
	
	protected $_layoutRender = 'auto';
	
	private static $_instance = null;
	
	
	/** ТОЧКА ВХОДА В КЛАСС (ПОЛУЧИТЬ ЭКЗЕМПЛЯР Layout) */
	public static function get(){
		
		if(is_null(self::$_instance))
			self::$_instance = new Layout();
		
		return self::$_instance;
	}
	
	/** КОНСТРУКТОР */
	protected function __construct(){
		
		$this->_tplPath = FS_ROOT.'templates/';
	}
	
	public function setTitle($title){
		
		$this->_htmlTitle = $title;
		return $this;
	}
	
	/** ПОЛУЧИТЬ ДИРЕКТОРИЮ ФАЙЛОВ МАКЕТА */
	public function getLayoutDir(){
		return $this->_layoutDir;
	}
	
	public function prependTitle($title, $separator = ' » '){
		
		$this->_htmlTitle = strlen($this->_htmlTitle)
			? $title.$separator.$this->_htmlTitle
			: $title.$this->_htmlTitle;
		return $this;
	}
	
	public function appendTitle($title, $separator = ' » '){
		
		$this->_htmlTitle = !empty($this->_htmlTitle)
			? $this->_htmlTitle.$separator.$title
			: $title;
		return $this;
	}
	
	public function setLinkTags($tags){
	
		foreach($tags as $tagname => $tagval)
			if(!empty($tagval))
				$this->_htmlLinkTags[$tagname] = $tagval;
				
		return $this;
	}
	
	public function setBreadcrumbs($mode, $data = array()){
		
		$appendData = FALSE;
		
		switch($mode){
			case 'auto':
				$this->setBreadcrumbsAuto();
				break;
			case 'auto-with':
				$this->setBreadcrumbsAuto();
				$appendData = TRUE;
				break;
			case 'set':
				$this->setBreadcrumbsAuto();
				$this->_breadcrumbs = array();
				$appendData = TRUE;
				break;
			case 'add':
				$appendData = TRUE;
				break;
			default: trigger_error('Неверный режим установки breadcrumbs. Допустимые значения: "set", "add", "auto", "auto-with"', E_USER_ERROR);
		}
		
		if($appendData){
			if(count($data) && !is_array($data[0])){
				$this->_breadcrumbs[] = $data;
			}else{
				foreach($data as $k => $v)
					$this->_breadcrumbs[] = $v;
			}
		}
		return $this;
	}
	
	public function setBreadcrumbsAuto(){
		
		if($this->_isAutoBreadcrumbsAdded)
			return;
			
		$this->_isAutoBreadcrumbsAdded = TRUE;
		$this->_breadcrumbs = array();
	}
	
	/** ОЧИСТИТЬ КОНТЕНТ */
	public function clearContent(){
	
		$this->_htmlContent = '';
		return $this;
	}
	
	/** ЗАДАТЬ КОНТЕНТ */
	public function setContent($content){
	
		$this->_htmlContent .= $content;
		return $this;
	}
	
	/** ПОЛУЧИТЬ КОНТЕНТ ИЗ ПРОИЗВОЛЬНОГО ФАЙЛА (БЕЗ ИНТЕРПРЕТАЦИИ) */
	public function setContentHtmlFile($file){
		
		$this->_htmlContent .= $this->getContentHtmlFile($file);
		return $this;
	}
	
	/** ПОЛУЧИТЬ КОНТЕНТ ИЗ PHP-ФАЙЛА (С ИНТЕРПРЕТАЦИЕЙ) */
	public function setContentPhpFile($file, $variables = array()){
		
		$this->_htmlContent .= $this->getContentPhpFile($file, $variables);
		return $this;
	}
	
	/** 
	 * ТИП ОТОБРАЖЕНИЯ КОНТЕНТА ВЫБИРАЕТСЯ АВТОМАТИЧЕСКИ
	 * внутри макета для обычных запросов;
	 * без макета для AJAX-запросов.
	 */
	public function autoLayout(){
		
		$this->_layoutRender = 'auto';
		return $this;
	}
	
	/** ВСЕГДА ОТОБРАЖАТЬ КОНТЕНТ ВНУТРИ МАКЕТА */
	public function enableLayout(){
		
		$this->_layoutRender = 'on';
		return $this;
	}
	
	/** ВСЕГДА ОТОБРАЖАТЬ КОНТЕНТ БЕЗ МАКЕТА */
	public function disableLayout(){
		
		$this->_layoutRender = 'off';
		return $this;
	}
	
	protected function _getHtmlTitle(){
		
		return !empty($this->_htmlTitle)
			? $this->_htmlTitle.' - '.CFG_SITE_NAME
			: CFG_SITE_NAME;
	}
	
	protected function _getHtmlLinkTags(){
		
		$output = '';
		foreach($this->_htmlLinkTags as $rel => $href)
			$output = "\t".'<link rel="'.$rel.'" href="'.$href.'" />'."\n";
		
		return $output;
	}
	
	/** GET BASE HREF URL */
	protected function _getHtmlBaseHref(){
		
		return WWW_ROOT;
	}
	
	/** GET BREADCRUMBS HTML */
	protected function _getBreadcrumbs(){
		
		$breadcrumbs = array();
		$num = count($this->_breadcrumbs);
		foreach($this->_breadcrumbs as $index => $v)
			$breadcrumbs[] = is_null($v[0]) || ($index + 1) == $num
				? '<span class="item">'.$v[1].'</span>'
				: '<a class="item" href="'.App::href($v[0]).'">'.$v[1].'</a>';
		
		return $num ? '<div class="breadcrumbs">'.implode('<span class="mediator"> » </span>', $breadcrumbs).'</div>' : '';
	}
	
	/** GET USER MESSAGE HTML */
	protected function _getUserMessages(){
	
		return Messenger::get()->getAll();
	}
	
	/** GET HTML CONTENT */
	protected function _getHtmlContent(){
		
		return $this->_htmlContent;
	}
	
	/** GET PHP PAGE STATISTICS HTML */
	protected function _getPhpPageStatistics(){
		
		$scriptExecutionTime = round(microtime(1) - $GLOBALS['__vikOffTimerStart__'], 4);
		
		$output = ''
			.'<table class="php-page-statistics">'
			.'<tr class="section"><th colspan="2" >PHP</th></tr>'
			.'<tr><th>Показатель</th><th>Значение</th></tr>'
			.'<tr><td>Версия интерпретатора</td><td>'.phpversion().'</td></tr>'
			.'<tr><td>Время выполнения скрипта</td><td>'.$scriptExecutionTime.' сек.</td></tr>'
			.'<tr><td>Подключенных файлов</td><td>'.count(get_included_files()).'</td></tr>'
			.'<tr><td>Пик использования памяти</td><td>'.Common::formatByteSize(memory_get_peak_usage()).'</td></tr>'
			
			.'<tr class="section"><th colspan="2" >SQL</th></tr>'
			.'<tr><th>Запрос</th><th>Время, сек</th></tr>'
		;
		foreach(db::get()->getQueriesWithTime() as $q)
			$output .= '<tr><td>'.$q['sql'].'</td><td>'.round($q['time'], 5).'</td></tr>';
		$output .= ''
			.'<tr class="b"><td>Всего запросов</td><td>'.db::get()->getQueriesNum().'</td></tr>'
			.'<tr class="b"><td>Общее время выполнения</td><td>'.round(db::get()->getQueriesTime(), 5).' сек.</td></tr>'
			.'</table>'
		;
		
		$output = '
			<script type="text/javascript">
			$(function(){
				VikDebug.print(\''.preg_replace(array("/\s+/", "/'/"), array(" ", "\\'"), $output).'\', "performance", {activateTab: false, onPrintAction: "none"});
			});
			</script>';
		return $output;
	}
	
	public function loginPage(){
		
		include($this->_tplPath.'login.php');
		exit();
	}
	
	/** RENDER ALL */
	public function render($boolReturn = FALSE){
		
		// вывод без макета
		if($this->_layoutRender == 'off' || ($this->_layoutRender == 'auto' && AJAX_MODE))
			return $this->_renderNoLayout($boolReturn);
		
		// вывод с макетом
		else
			return $this->_renderWithLayout($boolReturn);
	}
	
	/**
	 * ВЫВЕСТИ/ВЕРНУТЬ КОНТЕНТ БЕЗ МАКЕТА
	 * @access protected
	 * @param bool $boolReturn - флаг, возвращать контент, или выводить
	 * @param void|string контент
	 */
	protected function _renderNoLayout($boolReturn){
		
		if($boolReturn)
			return $this->_getHtmlContent();
		else
			echo $this->_getHtmlContent();
	}
	
	/**
	 * ВЫВЕСТИ/ВЕРНУТЬ КОНТЕНТ В МАКЕТЕ
	 * @access protected
	 * @param bool $boolReturn - флаг, возвращать контент, или выводить
	 * @param void|string контент
	 */
	protected function _renderWithLayout($boolReturn){
		
		if($boolReturn)
			ob_start();
			
		include($this->_tplPath.'layout.php');
		
		if($boolReturn)
			return ob_get_clean();
	}
	
	/** АКСЕССОР ДЛЯ ШАБЛОНОВ */
	public function __get($name){
		
		return isset($this->$name) ? $this->$name : '';
	}
	
	/** ПОЛУЧИТЬ СОДЕРЖИМОЕ HTML ФАЙЛА */
	public function getContentHtmlFile($file){
		
		return file_get_contents($this->_tplPath.$file);
	}
	
	/** ПОЛУЧИТЬ ПРОИНТЕРПРЕТИРОВАННОЕ СОДЕРЖИМОЕ PHP ФАЙЛА */
	public function getContentPhpFile($file, $variables = array()){
		
		foreach($variables as $k => $v)
			$this->$k = $v;
			
		ob_start();
		include($this->_tplPath.$file);
		
		foreach($variables as $k => $v)
			unset($this->$k);
		
		return ob_get_clean();
	}
}

?>