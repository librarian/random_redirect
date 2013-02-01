<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * MaxSite CMS
 * (c) http://max-3000.com/
 */


# функция автоподключения плагина
function random_redirect_autoload($args = array())
{
	mso_hook_add('custom_page_404', 'random_redirect_custom_page_404'); # хук для подключения к шаблону
}

# функция выполняется при активации (вкл) плагина
function random_redirect_activate($args = array())
{	
	mso_create_allow('random_redirect_edit', t('Админ-доступ к гостевой книге'));
	
		
	return $args;
}

function random_redirect_mso_options()
{

    # ключ, тип, ключи массива
    mso_admin_plugin_options('plugin_random_redirect', 'plugins',
        array(
            'random_redirect_width' => array(
                            'type' => 'text',
                            'name' => t('URL по которому будет происходить перенаправление'),
                            'description' => '',
                            'default' => 'random'
                        ),
            ),
        t('Настройки плагина случайного перенаправления'),
        t('Укажите необходимые опции.')
    );
}


# функция выполняется при деинстяляции плагина
function random_redirect_uninstall($args = array())
{	
	mso_delete_option('plugin_random_redirect', 'plugins' ); // удалим созданные опции
	mso_remove_allow('random_redirect_edit'); // удалим созданные разрешения
	

	return $args;
}


# подключаем свой файл к шаблону
function random_redirect_custom_page_404($args = false)
{
	$options = mso_get_option('plugin_random_redirect', 'plugins', array());
	if ( !isset($options['slug']) ) $options['slug'] = 'random'; 
	
    $CI = & get_instance();
    
    $CI->db->select('page_id,page_slug');
    $CI->db->where('page_date_publish < ', 'NOW()', false);
    $CI->db->where('page_status', 'publish');
    $CI->db->join('page_type', 'page_type.page_type_id = page.page_type_id');
    $CI->db->from('page');
    $CI->db->order_by('page_id', 'random');
    $CI->db->limit(1);
    
    $query = $CI->db->get();
    
    if ($query->num_rows() > 0) 
    {   
        $slug = $query->result_array();
        if ( mso_segment(1) == $options['slug'] ) 
        {
            header('Location: ' . getinfo('siteurl') . 'page/' . $slug[0]['page_slug']);
            return true;
        }
    }

	return $args;
}

?>
