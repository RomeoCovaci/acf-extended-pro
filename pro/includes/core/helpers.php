<?php

if(!defined('ABSPATH'))
    exit;

// Register store
acf_register_store('local-templates');

/*
 * Get Local Templates
 */
function acfe_get_local_templates(){
    return acf_get_local_store('templates')->get();
}

/*
 * Get Local Template
 */
function acfe_get_local_template($name = ''){
    return acf_get_local_store('templates')->get($name);
}

/*
 * Remove Local Template
 */
function acfe_remove_local_template($name = ''){
    return acf_get_local_store('templates')->remove($name);
}

/*
 * Have Local Templates
 */
function acfe_have_local_templates() {
    return acf_get_local_store('templates')->count() ? true : false;
}

/*
 * Is Local Template
 */
function acfe_is_local_template($name = ''){
    return acf_get_local_store('templates')->has($name);
}

/*
 * Count Local Template
 */
function acfe_count_local_templates(){
    return acf_get_local_store('templates')->count();
}

/*
 * Add Local Template
 */
function acfe_add_local_template($args = array()){
    
    $args = wp_parse_args($args, array(
        'title'     => '',
        'name'      => '',
        'active'    => true,
        'values'    => array(),
        'location'  => array(),
    ));
    
    acf_get_local_store('templates')->set($args['name'], $args);
    
    return true;
    
}

/*
 * Has Flexible Grid
 */
if(!function_exists('has_flexible_grid')){

function has_flexible_grid($name, $post_id = false){
    
    // get field
    $field = acf_maybe_get_field($name, $post_id);
    
    // bail early
    if(!$field)
        return false;
    
    // vars
    $flexible_grid = acf_maybe_get($field, 'acfe_flexible_grid');
    $flexible_grid_enabled = acf_maybe_get($flexible_grid, 'acfe_flexible_grid_enabled');
    
    // not enabled
    if(!$flexible_grid_enabled)
        return false;
    
    // return
    return true;
    
}

}

/*
 * Get Flexible Grid
 */
if(!function_exists('get_flexible_grid')){

function get_flexible_grid($name, $post_id = false){
    
    // bail early
    if(!has_flexible_grid($name, $post_id))
        return false;
    
    // vars
    $field = acf_maybe_get_field($name, $post_id);
    $flexible_grid = acf_maybe_get($field, 'acfe_flexible_grid');
    $flexible_grid_enabled = acf_maybe_get($flexible_grid, 'acfe_flexible_grid_enabled');
    
    // not enabled
    if(!$flexible_grid_enabled)
        return false;
    
    // return data
    return array(
        'align'     => $flexible_grid['acfe_flexible_grid_align'],
        'valign'    => $flexible_grid['acfe_flexible_grid_valign'],
        'wrap'      => $flexible_grid['acfe_flexible_grid_wrap'],
        'container' => $field['acfe_flexible_grid_container'],
    );
    
}

}

/*
 * Get Flexible Grid Class
 */
if(!function_exists('get_flexible_grid_class')){

function get_flexible_grid_class($name, $post_id = false){
    
    // get field
    $grid = get_flexible_grid($name, $post_id);
    
    // bail early
    if(!$grid)
        return false;
    
    // vars
    $class = "align-{$grid['align']} valign-{$grid['valign']}";
    $class .= $grid['wrap'] ? " wrap" : "";
    
    //return
    return $class;
    
}

}

/*
 * Get Layout Col
 */
if(!function_exists('get_layout_col')){

function get_layout_col(){
    return get_sub_field('acfe_layout_col');
}

}

/*
 * Get Countries
 */
function acfe_get_countries($args = array()){
    
    // Default args
    $args = wp_parse_args($args, array(
        'type'          => 'countries',
        'code__in'      => false,
        'name__in'      => false,
        'continent__in' => false,
        'language__in'  => false,
        'currency__in'  => false,

        'orderby'       => false,
        'order'         => 'ASC',
        'offset'        => 0,
        'limit'         => -1,

        'field'         => false,
        'display'       => false,
        'prepend'       => false,
        'append'        => false,
        'groupby'       => false,
    ));
    
    // Query
    $query = new ACFE_World_Query($args);
    
    // Results
    return $query->data;
    
}

/*
 * Get Country
 */
function acfe_get_country($code){
    
    $data = acfe_get_countries(array(
        'code__in'  => $code,
        'limit'     => 1
    ));
    
    return reset($data);
    
}

/*
 * Get Languages
 */
function acfe_get_languages($args = array()){
    
    // Default args
    $args = wp_parse_args($args, array(
        'type'              => 'languages',
        'name__in'          => false,
        'locale__in'        => false,
        'alt__in'           => false,
        'code__in'          => false,
        'continent__in'     => false,
        'country__in'       => false,
        'currency__in'      => false,
        
        'orderby'           => false,
        'order'             => 'ASC',
        'offset'            => 0,
        'limit'             => -1,

        'field'             => false,
        'display'           => false,
        'prepend'           => false,
        'append'            => false,
        'groupby'           => false,
    ));
    
    // Query
    $query = new ACFE_World_Query($args);
    
    // Results
    return $query->data;
    
}

/*
 * Get Language
 */
function acfe_get_language($locale){
    
    $data = acfe_get_languages(array(
        'locale__in'  => $locale,
        'limit'       => 1
    ));
    
    return reset($data);
    
}

/*
 * Get Currencies
 */
function acfe_get_currencies($args = array()){
    
    // Default args
    $args = wp_parse_args($args, array(
        'type'          => 'currencies',
        'name__in'      => false,
        'code__in'      => false,
        'continent__in' => false,
        'country__in'   => false,
        'language__in'  => false,
        
        'countries'     => false,
        'languages'     => false,
        
        'orderby'       => false,
        'order'         => 'ASC',
        'offset'        => 0,
        'limit'         => -1,

        'field'         => false,
        'display'       => false,
        'prepend'       => false,
        'append'        => false,
        'groupby'       => false,
    ));
    
    // Query
    $query = new ACFE_World_Query($args);
    
    // Results
    return $query->data;
    
}

/*
 * Get Currency
 */
function acfe_get_currency($code){
    
    $data = acfe_get_currencies(array(
        'code__in'  => $code,
        'limit'     => 1
    ));
    
    return reset($data);
    
}