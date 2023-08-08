jQuery(document).ready(function($) {
    // Captura el evento de envío del formulario
    $('#bluecell-form').submit(function(event) {
        event.preventDefault();

        // Realiza la validación del formulario antes de enviarlo
        if ($('#bluecell-form')[0].checkValidity()) {
            var formData = $(this).serialize();
            formData += '&action=bluecell_process_form';

            // Realiza la solicitud AJAX para enviar los datos del formulario
            $.ajax({
                type: 'POST',
                url: bluecell_ajax_object.ajax_url,
                data: formData,
                success: function(response) {
                    // Aquí puedes mostrar un mensaje de éxito o redirigir a una página de agradecimiento
                },

                error: function(error) {
                    // Aquí puedes mostrar un mensaje de error o manejar el error de otra manera
                }
            });
        } else {
            // Si el formulario no es válido, muestra un mensaje de error o realiza alguna acción adicional
        }
    });
});