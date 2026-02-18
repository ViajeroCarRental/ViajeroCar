$(document).ready(function(){

  function actualizarIcono(selectElement) {

    const text = $(selectElement).find('option:selected').text().toLowerCase();
    let icon = 'fa-location-dot';

    if (text.includes('aeropuerto')) icon = 'fa-plane-departure';
    else if (text.includes('autobus') || text.includes('autobuses')) icon = 'fa-bus';
    else if (text.includes('oficina')) icon = 'fa-building';

    $(selectElement)
      .closest('.ctl')
      .find('.ico i')
      .attr('class', 'fa-solid ' + icon);
  }

  $('#pickupPlace').on('change', function(){
    actualizarIcono(this);
  });

  $('#dropoffPlace').on('change', function(){
    actualizarIcono(this);
  });

});
