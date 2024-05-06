/**
 *
 *
 * @module     block_queries
 * @package
 * @copyright  2022
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/modal_factory', 'core/modal_events'],
        function($, ModalFactory, ModalEvents) {

    return {
        init: function() {
            var self = this;
            $('.commenticonpostion').click(function(){
                var queryid = $(this).data('id');
                var formbody = $('#basicModal'+queryid).html();
                ModalFactory.create({
                    type: ModalFactory.types.DEFAULT,
                    title: 'Post Reply',
                    body: formbody,
                    footer: '',
                }).done(function(modal) {
                    // Keep a reference to the modal.
                    self.modal = modal;
                    // We want to reset the form every time it is opened.
                    self.modal.getRoot().on(ModalEvents.hidden, function() {
                        // self.modal.setBody('');
                        self.modal.hide();
                        self.modal.destroy();
                        window.location.reload();
                    }.bind(this));
                    self.modal.show();
                    $('#basicModal'+queryid).remove();
                });
            });
        },
    };
});
