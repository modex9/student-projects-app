/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/global.scss';

// start the Stimulus application
import './bootstrap';

// this loads jquery, but does *not* set a global $ or jQuery variable
const $ = require('jquery');
Window.prototype.$ = $;

$(function () {
    $('select').on('change', function() {
        $(".alert-danger").text('');
        $(".alert-danger").addClass('d-none');
        const studentId = $(this).val();
        const groupId = $(this).closest('table').data('group-id');
        fetch(`/student/${studentId}/assign/${groupId}`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
        }).then(response => response.json())
          .then(function (json) {
                if(json.error)
                {
                    $(".alert-danger").text(json.error);
                    $(".alert-danger").removeClass('d-none');
                }
          });
    });
});