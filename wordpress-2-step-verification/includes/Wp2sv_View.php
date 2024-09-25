<?php


class Wp2sv_View
{
	protected $name;
	protected $data;
	protected $extensions=['.php'];
	public function __construct($name,$data=[],$mergeData=[])
	{
		$this->name=$name;
		$this->data=array_merge($data,$mergeData);
	}
	public static function make($name,$data=[],$mergeData=[]){
		return new static($name,$data,$mergeData);
	}
	public function getData(){
		return $this->data;
	}

	public function render(){
		return $this->getContent();
	}
	protected function getContent(){
		ob_start();
		extract($this->getData());
		if($__template_file=$this->locateTemplate($this->name)){
			include $__template_file;
		}else{echo $__template_file;};
		return ob_get_clean();
	}
	public function __toString()
	{
		return $this->render();
	}
	protected function locateTemplate($template_name, $template_path='', $default_path=''){
		$template_name=str_replace('.','/',$template_name);
		if ( ! $template_path ) {
			$template_path = 'wp2sv';
		}

		if ( ! $default_path ) {
			$default_path = trailingslashit(WP2SV_TEMPLATE);
		}
		$templates=$this->applyExtensions(array(
			trailingslashit( $template_path ) . $template_name,
		));
		// Look within passed path within the theme - this is priority.
		$template = locate_template($templates);

		// Get default template/.
		if ( ! $template ) {
			$templates = $this->applyExtensions($default_path . $template_name);
			foreach ($templates as $template){
				if(file_exists($template)){
					break;
				}
			}
		}
		return $template;
	}
	protected function applyExtensions($names){

		$templates=array();
		foreach ((array)$names as $name){
			foreach ($this->extensions as $extension){
				$templates[]=$name.$extension;
			}
		}
		return $templates;
	}
}