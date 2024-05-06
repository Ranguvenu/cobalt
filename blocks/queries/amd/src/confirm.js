/**
 *
 * @module     block_queries
 * @package
 * @copyright  2022
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 define(['jquery', 'core/modal_factory', 'core/str', 'core/modal_events', 'core/ajax', 'core/notification'],
  function($, ModalFactory, String, ModalEvents, Ajax, Notification) {
    var trigger = $('.local_message_delete_button');
    ModalFactory.create({
        type: ModalFactory.types.SAVE_CANCEL,
        title: String.get_string('deletequery', 'block_queries'),
        body: String.get_string('deletequeryconfirm', 'block_queries'),
        preShowCallback: function(triggerElement, modal) {
            // Do something before we show the delete modal.
            triggerElement = $(triggerElement);

            let classString = triggerElement[0].classList[0]; // local_messageid13
            // let queryid = classString.substr(classString.lastIndexOf('local_queryid') + 'local_queryid'.length);
            let responseid = classString.substr(classString.lastIndexOf('local_responseid') + 'local_responseid'.length);
            // Set the message id in this modal.
            if(responseid){
                modal.params = {'responseid': responseid};
            }

            modal.setSaveButtonText(String.get_string('deletequerybtn', 'block_queries'));
        },
        large: false,
    }, trigger)
        .done(function(modal) {
            // Do what you want with your new modal.
            modal.getRoot().on(ModalEvents.save, function(e) {
                // Stop the default save button behaviour which is to close the modal.
                e.preventDefault();

                let footer = Y.one('.modal-footer');
                footer.setContent(String.get_string('deleting', 'block_queries'));
                let spinner = M.util.add_spinner(Y, footer);
                spinner.show();
                let request = {
                    methodname: 'block_queries_delete_query',
                    args: modal.params,
                };
                Ajax.call([request])[0].done(function(data) {
                    modal.hide();
                    window.location.reload();
                }).fail(Notification.exception);
            });
        });

            var trigger = $('.local_message_delete_response');
            ModalFactory.create({
                type: ModalFactory.types.SAVE_CANCEL,
                title: String.get_string('deletequery', 'block_queries'),
                body: String.get_string('deleteresponseconfirm', 'block_queries'),
                preShowCallback: function(triggerElement, modal) {
                    // Do something before we show the delete modal.
                    triggerElement = $(triggerElement);

                    let classString = triggerElement[0].classList[0]; // local_messageid13
                    let queryid = classString.substr(classString.lastIndexOf('local_queryid') + 'local_queryid'.length);
                    // let responseid = classString.substr(classString.lastIndexOf('local_responseid') + 'local_responseid'.length);
                    // Set the message id in this modal.
                    if(queryid){
                        modal.params = {'queryid': queryid};
                    }
                    modal.setSaveButtonText(String.get_string('deletequerybtn', 'block_queries'));
                },
                large: false,
            }, trigger)
                .done(function(modal) {
                    // Do what you want with your new modal.
                    modal.getRoot().on(ModalEvents.save, function(e) {
                        // Stop the default save button behaviour which is to close the modal.
                        e.preventDefault();

                        let footer = Y.one('.modal-footer');
                        footer.setContent(String.get_string('deleting', 'block_queries'));
                        let spinner = M.util.add_spinner(Y, footer);
                        spinner.show();
                        let request = {
                            methodname: 'block_queries_delete_responce',
                            args: modal.params,
                        };
                        Ajax.call([request])[0].done(function(data) {
                            modal.hide();
                            window.location.reload();
                        }).fail(Notification.exception);
                    });
                });
        });
