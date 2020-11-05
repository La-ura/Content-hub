<?
/*
Plugin Name: Content hub
Description: Importa notas de las agencias :Europress , Notimex ,EFE, Xinhua ,AFP , AP ,DPA , APTN ,Reuters 
Version:1
Author:Laura Ramírez
License: GPL
*/
// in the main plugin file
define( 'MYPLUGIN_FILE', __FILE__ );
//no se genera categorias para noticieros televisa
//register_activation_hook(MYPLUGIN_FILE, 'insert_category' );
add_action('admin_menu', 'add_botton_import_article' );
/* Guarda en wp el articulo */
add_action('wp_ajax_nopriv_import_note_from_api','api_save_cont');
add_action('wp_ajax_import_note_from_api','api_save_cont');
/* Limpia campos de caracteres no validos */
add_action('wp_ajax_nopriv_clean_data','clean_fields');
add_action('wp_ajax_clean_data','clean_fields');

function add_botton_import_article(){
    add_menu_page("Importar Articulos de agencias","Content Hub",'manage_options','importar-articulos', 'get_articles' ,"dashicons-align-none");
   
}
function clean_field($str){
  $healthy = array("<headline>" , "</headline>" , "^V^V^Aa^_-r i^S^Q");
  $newphrase = str_replace($healthy, "", $str);
  return $newphrase ;
}
function api_save_cont(){
  $postArray = $_POST["datos"];
  $category =0 ;
  $title = clean_field($postArray["title"]);
  $conte = clean_field($postArray["content"]);
  $category = $postArray["category"];
  /* Otros sitios  */
  //$idcategory = get_category_id($category);
  //** solo para noticieros televisa *//
  $idcategory = get_taxanomia_nt($category );
  
  $my_post = array(
    'post_title'    =>  $title ,
    'post_content'  =>   $conte ,
    'post_author'   => get_current_user_id(),
    //'post_category' => array( $idcategory ),
    'tax_input' => array( 'topico' => array($idcategory)), 
    'post_type' =>"agencias"  //solo para noticieros televisa
  );
  $id = wp_insert_post( $my_post );
  $url = get_edit_post_link($id , "");
  echo  $url ;
  //echo "Id" . $id  ."   cat ". $idcategory ;
  wp_die();
}
// Esta funcion solo es para noticieros televisa
function get_taxanomia_nt($categorieAgencie){
      switch ($categorieAgencie) {
          case 'Ambiente':
            $term  = get_term_by( 'slug','clima-fenomenos-naturales', 'topico');  
           $taxonomiaId=$term->term_id;
          break;
          case 'Deportes':
             $term  = get_term_by( 'slug','deportes', 'topico');  
             $taxonomiaId=$term->term_id;
          break;  
          case 'Ciencia':
            $term  = get_term_by( 'slug','ciencia-y-tecnologia', 'topico');  
            $taxonomiaId=$term->term_id;
         break; 
         case 'Tecnologia':
          $term  = get_term_by( 'slug','tecnologia', 'topico');  
          $taxonomiaId=$term->term_id;
       break;  
        case 'Entretenimiento':
          $term  = get_term_by( 'slug','entretenimiento-y-espectaculos', 'topico');  
          $taxonomiaId=$term->term_id;
        break;
        case 'Salud':
          $term  = get_term_by( 'slug','salud', 'topico');  
          $taxonomiaId=$term->term_id;
        break;
      }
      return $taxonomiaId;
}

function get_category_id($category_json){
  $parent = get_term_by( 'name', 'Agencia', 'category');
  $term = term_exists( $category_json,  'category', $parent->term_id);
  return $term['term_id'];
}

function insert_category(){
  $term = term_exists( 'agencias',  'category' );
   if( $term === NULL ){
       wp_insert_term('Agencias',  'category', 
       array('slug' => 'agencias',    ));
 
       wp_insert_term(  'Deportes',   'category',
         array( 'slug' => 'deportes',  'parent'=> term_exists( 'Agencias', 'category' )['term_id'] ));
 
       wp_insert_term( 'Internacional', 'category',
          array('slug' => 'Internacional',  'parent'=> term_exists( 'Agencias', 'category' )['term_id']  ));
     
       wp_insert_term( 'Tecnología', 'category',
       array('slug' => 'tecnologia',  'parent'=> term_exists( 'Agencias', 'category' )['term_id']  ));
 
       wp_insert_term( 'Ambiente', 'category',
       array('slug' => 'ambiente',  'parent'=> term_exists( 'Agencias', 'category' )['term_id']  )); 
      
       wp_insert_term( 'Entretenimiento', 'category',
       array('slug' => 'entretenimiento',  'parent'=> term_exists( 'Agencias', 'category' )['term_id']  ));  
       
       wp_insert_term( 'Ciencia', 'category',
       array('slug' => 'ciencia',  'parent'=> term_exists( 'Agencias', 'category' )['term_id']  ));    
 
       wp_insert_term( 'Salud', 'category',
       array('slug' => 'salud',  'parent'=> term_exists( 'Agencias', 'category' )['term_id']  ));    
   }
  }

function get_articles(){
    wp_enqueue_script("jquery");
    wp_enqueue_script("jquery-ui-datepicker");
    wp_enqueue_script("jquery-ui-accordion");
    wp_enqueue_script("jquery-ui-selectmenu");
    wp_enqueue_script("jquery-ui-button");
    wp_enqueue_script("jquery-ui-dialog");
    wp_enqueue_script("import-article-api" ,  plugin_dir_url( __FILE__ ) . 'js/import-article-api.js');
    //wp_register_style('note-jquery-ui-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/blitzer/jquery-ui.css', false, null);
    //  wp_enqueue_style('note-jquery-ui-style');
    wp_register_style('note-jquery-ui-style',   plugin_dir_url( __FILE__ ) . 'css/jquery-ui-blitzer.css' );
    wp_enqueue_style('note-jquery-ui-style');
    wp_register_style('note-api-styles',  plugin_dir_url( __FILE__ ) . 'css/style.css' );
    wp_enqueue_style('note-api-styles');
   
   ?>


<div id="wrap">
<h1>Content Hub</h1>
<div id="lb_content_row1">
  <div class ="tb1">T&iacute;tulo   </div>
  <div class ="tb2">Fecha Inicio</div>
  <div class ="tb3">Fecha Final</div>
  <div class ="tb4">Categor&iacute;a</div>
  <div class ="tb5"> Agencias</div>
  <div class ="tb6"> Items</div>
</div>
  <div id="lb_content_row2">
      <div id="lb_cont_title"><input type="text" id="note_title" placeholder="Buscar por t&iacute;tulo" ></div>
      <div id="lb_date1">  <input type="text" id="date_start" placeholder=" yyy/mm/dd"></div>
      <div id="lb_date2"> <input type="text" id="date_end" placeholder=" yyy/mm/dd"></div>
      <div id="lb_category">
        <select name="note_category" id="note_category">
              <option value=""> Selecciona una categor&iacute;a</option>
              <option value="Ambiente">Ambiente</option>
              <option value="Ciencia">Ciencia</option>
              <option value="Deportes">Deportes</option>
              <option value="Entretenimiento">Entretenimiento</option>
              <option value="Internacional">Internacional</option>
              <option value="Salud">Salud</option>
              <option value="Tecnologia">Tecnolog&iacute;a</option>
            </select>
      </div>
      <div id="lb_agencie">
        <select name="note_agencie" id="note_agencie">
              <option value="">Selecciona una agencia</option>
              <option value="afp">AFP</option>
              <option value="ap">AP</option>
              <option value="APTN">APTN</option>
              <option value="DPA">DPA</option>
              <option value="EFE">EFE</option>
              <option value="Europress">EUROPRESS</option>
              <option value="Notimex">NOTIMEX</option>
              <option value="Reuters">REUTERS</option>
              <option value="Xinhua">XINHUA</option>
        </select> 
      </div>

      <div id="lb_itmes"> 
      <select name="note_items" id="note_items">
              <option value="20">20</option>
              <option value="40">40</option>
              <option value="60">60</option>
              <option value="80">80</option>
              <option value="100">100</option>
      </select>
      </div>
  </div>
 <div id="lb_cont_btn"> <button id ="search_note" class="ui-button ui-widget ui-corner-all" onclick="show_article()" >Buscar </button></div>
    <div id="dialog-confirm" style ="display:none; " title="Importar artículo">
  <div><span class="" style="float:left; margin:12px 12px 20px 0;"></span>¿Está seguro de importar este artículo?</div>
</div>
    <div id="content-list-notes"></div>
</div>
</div>
   <?

}