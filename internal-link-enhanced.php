<?php
/*
Plugin Name: Inernal Link Enhanced
Plugin URI: http://goo.gl/IB1zbU
Description: Améliorer les résultats de recherche des liens internes (en modifiant le comportement de la recherche afin de pouvoir trouver un article par rapport à son titre et en intégrant les articles en attente de publication)
Version: 1.0
Author: Julio Potier
Author URI: http://BoiteAWeb.fr
Licence: CC BY-NC-SA 4.0 http://creativecommons.org/licenses/by-nc-sa/4.0/
*/
/*  Copyright 2014 Yvan GODARD (godardyvan@gmail.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of Creative Commons BY-NC-SA 4.0 licence
    http://creativecommons.org/licenses/by-nc-sa/4.0/
    THIS SOFTWARE IS PROVIDED BY THE REGENTS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, 
    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS 
    FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE REGENTS AND CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
    INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, 
    PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
    HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT 
    (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, 
    EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

if( is_admin() ):
function baw_better_internal_link_search( $search, &$wp_query ) {
	
    global $wpdb;
    
    // si pas de recherche, je la renvoie vide
    if( !isset( $_POST['action'] ) || $_POST['action']!='wp-link-ajax' || empty( $search ) )
             return $search;
 
    // Selon si la recherche est une recherche exacte ou pas, j'ajouterai des % dans mon LIKE
    $n = !empty( $wp_query->query_vars['exact'] ) ? '' : '%';
    
    //Init pour éviter des notices
    $search = '';
    $searchand = '';
        
    /* ASTUCE 1 */
    // Pour chaque term passé dans la recherche, je vais les sanitizer avec esc_sql et like_escape
    // puis je construis ma chaîne de recherche SEULEMENT dans post_title !
    foreach( (array) $wp_query->query_vars['search_terms'] as $term ):
	$term = esc_sql( like_escape( $term ) );
	$search.= "{$searchand}(($wpdb->posts.post_title LIKE '{$n}{$term}{$n}'))";
	$searchand = ' AND ';
    endforeach;
 
    // Cette fois si la recherche n'est pas vide, je l'ajoute
    if ( !empty( $search ) )
	 $search = " AND ({$search}) ";
 
    /* ASTUCE 2 */
    // Je récupère les status des posts filtrés dans la requête et j'ajoute si besoin le status "FUTURE"
    // afin de pouvoir faire des liens vers mes articles prévus dans le futur.
    $post_status = (array)$wp_query->query_vars['post_status'];
    if ( !in_array( 'future', $post_status ) ):
	$post_status[] = 'future';
	$wp_query->set( 'post_status', $post_status );
    endif;
    
    // Et on renvoie la recherche
    return $search;
}
add_filter('posts_search', 'baw_better_internal_link_search', 10, 2 );
endif;