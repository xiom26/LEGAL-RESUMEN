jQuery(document).ready(function($){
  function cargarResumen(){
    $.post(GUCResumenAjax.ajaxurl, {action: 'guc_get_resumen'}, function(resp){
      if(!resp) return;

      $('#total-inicio').text(resp.totales.Inicio || 0);
      $('#total-proceso').text((resp.totales['En proceso'] || 0));
      $('#total-terminado').text(resp.totales.Terminado || 0);

      const tbody = $('#tabla-casos tbody');
      tbody.empty();
      (resp.casos || []).forEach(function(c){
        const fecha = formatearFecha(c.estado_fecha);
        const tr = `<tr>
          <td>${c.expediente || ''}</td>
          <td>${fecha}</td>
          <td>${c.estado || ''}</td>
        </tr>`;
        tbody.append(tr);
      });
    }, 'json');
  }

  cargarResumen();

  function formatearFecha(fecha){
    if(!fecha) return '';
    const texto = String(fecha).trim();
    return texto.length >= 10 ? texto.slice(0,10) : texto;
  }
});
