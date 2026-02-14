$(document).ready(function() {

  function formatOption(option) {
    if (!option.id) {
      // Placeholder icono de mapa
      return $('<span class="icon-item"><i class="fa-solid fa-location-dot"></i> ' + option.text + '</span>');
    }

    let iconClass = 'fa-location-dot'; 
    const text = option.text.toLowerCase();

    if (text.includes('aeropuerto')) { 
      iconClass = 'fa-plane-departure'; // AVION
    } else if (text.includes('central de autobuses')) {
      iconClass = 'fa-bus'; // AUTOBUS 
    } else if (text.includes('oficina') || text.includes('central park')) {
      iconClass = 'fa-building';// EDIFICIO
    }

    return $(
        '<span class="icon-item"><i class="fa-solid ' +   iconClass + '"></i> ' + // icono dinámico
         option.text + // nombre de la sucursal
         '</span>');
  }

$('#pickupPlace, #dropoffPlace').select2({
  templateResult: formatOption,
  templateSelection: formatOption,
  escapeMarkup: function(markup) { return markup; },
  width: '100%',
  minimumResultsForSearch: Infinity
});

});
