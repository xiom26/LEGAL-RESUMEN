<?php
/**
 * Plugin Name: Resumen
 * Description: Listado de casos con estados.
 * Version: 1.0.0
 * Author: Inecxus
 */

if (!defined('ABSPATH')) exit;

class GUC_Resumen_Plugin {
  private static $instance = null;
  public static function instance(){ return self::$instance ?: self::$instance = new self(); }

  private function __construct(){
    add_action('init', [$this,'register_assets']);
    add_shortcode('guc_resumen', [$this,'shortcode']);

    // AJAX
    add_action('wp_ajax_guc_get_resumen',        [$this,'ajax_get_resumen']);
    add_action('wp_ajax_nopriv_guc_get_resumen', [$this,'ajax_get_resumen']);
  }

  public function register_assets(){
    wp_register_style('guc-resumen-css', plugin_dir_url(__FILE__).'guc-resumen.css', [], '1.0.3');
    wp_register_script('guc-resumen-js', plugin_dir_url(__FILE__).'guc-resumen.js', ['jquery'], '1.0.3', true);
    wp_localize_script('guc-resumen-js', 'GUCResumenAjax', [
      'ajaxurl' => admin_url('admin-ajax.php')
    ]);
  }

  public function shortcode(){
    wp_enqueue_style('guc-resumen-css');
    wp_enqueue_script('guc-resumen-js');

    ob_start(); ?>
    <div id="guc-resumen" class="guc-wrap">
      <h3 class="guc-title">Estadísticas Generales</h3>

      <div class="guc-cards">
        <div class="guc-card">
          <div class="label">Total de casos en: <b>INICIO</b></div>
          <div class="value" id="total-inicio">0</div>
        </div>
        <div class="guc-card">
          <div class="label">Total de casos en: <b>PROCESO</b></div>
          <div class="value" id="total-proceso">0</div>
        </div>
        <div class="guc-card">
          <div class="label">Total de casos en: <b>TERMINADO</b></div>
          <div class="value" id="total-terminado">0</div>
        </div>
      </div>

      <div class="guc-table-wrap">
        <table class="guc-table" id="tabla-casos">
          <thead>
            <tr>
              <th>EXPEDIENTE</th>
              <th>FECHA</th>
              <th>ESTADO</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
    <?php
    return ob_get_clean();
  }

  public function ajax_get_resumen(){
    global $wpdb;
    $tabla = $wpdb->prefix . 'guc_cases';

    // Leer solo casos existentes (si se elimina de la tabla, ya no aparecerá aquí).
    $rows = $wpdb->get_results($wpdb->prepare("
      SELECT expediente, estado_fecha, estado
      FROM {$tabla}
      WHERE expediente IS NOT NULL AND expediente <> ''
      ORDER BY estado_fecha DESC
    "));

    // Totales por estado
    $tot = ['Inicio'=>0,'En proceso'=>0,'Terminado'=>0];
    foreach ($rows as $r) {
      $e = isset($r->estado) ? $r->estado : '';
      if (isset($tot[$e])) $tot[$e]++;
    }

    wp_send_json([
      'totales' => $tot,
      'casos'   => $rows
    ]);
  }
}

GUC_Resumen_Plugin::instance();
