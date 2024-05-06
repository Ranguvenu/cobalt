define(['jquery', 'core/str', 'core/modal_factory', 'core/modal_events',  'core/ajax'],
    function($, Str, ModalFactory, ModalEvents, Ajax) {
        var SELECTOR = "a[data-action='deletevideo-favourite']";
        return {
            init: function(){
                $(SELECTOR).on('click', function(){
                    var dataid = $(this).data('id');
                    return Str.get_strings([{
                        key: 'deletevideo',
                        component: 'block_stream'
                    },
                    {
                        key: 'deleteconfirm',
                        component: 'block_stream'
                    },
                    {
                        key: 'confirm'
                    }
                    ]).then(function(s) {
                        ModalFactory.create({
                            title: s[0],
                            type: ModalFactory.types.SAVE_CANCEL,
                            body: s[1]
                        }).done(function(modal) {
                            this.modal = modal;
                            modal.setSaveButtonText(s[2]);
                            modal.getRoot().on(ModalEvents.save, function(e) {
                                e.preventDefault();
                                var params = {};
                                params.id = dataid;
                                params.contextid = 1;

                                var promise = Ajax.call([{
                                    methodname: 'block_stream_delete_video',
                                    args: params
                                }]);
                                promise[0].done(function(resp) {
                                    modal.hide();
                                    window.location.reload();
                                }).fail(function(ex) {
                                    // do something with the exception
                                     console.log(ex);
                                });
                            }.bind(this));
                            modal.show();
                        }.bind(this));
                    }.bind(this));
                });
            }
        }
});