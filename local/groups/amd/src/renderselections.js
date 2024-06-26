define(['jquery', 'core/str', 'core/modal_factory', 'core/modal_events'],
        function($, Str, ModalFactory) {
    return {
        init: function() {

        },
        deletecohort: function(elem, name) {
            return Str.get_strings([{
                key: 'delcohort',
                component: 'local_groups'
            }, {
                key: 'delconfirm',
                component: 'local_groups',
                param:name
            },
            {
                key: 'delete',
                component: 'local_groups',
            },
            {
                key: 'cancel',
                component: 'local_groups',
            }]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    type: ModalFactory.types.DEFAULT,
                    body: s[1],
                    footer: '<button type="button" class="btn btn-secondary" data-action="cancel">'
                                +M.util.get_string("cancel", "local_groups")+
                            '</button>&nbsp;' +
                            '<button type="button" class="btn btn-primary" data-action="save">'
                                +M.util.get_string("delete", "local_groups")+
                            '</button>'
                }).done(function(modal) {
                    this.modal = modal;
                    modal.getRoot().find('[data-action="save"]').on('click', function() {
                        window.location.href = M.cfg.wwwroot +'/local/groups/edit.php?id='
                                                                +elem+'&confirm=1&delete=1&sesskey='
                                                                + M.cfg.sesskey;
                    }.bind(this));
                    modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                        modal.setBody('');
                        modal.hide();
                    });
                    modal.show();
                }.bind(this));
            }.bind(this));
        },

        notdeletecohort: function(elem, name) {
            return Str.get_strings([{
                key: 'notdelcohort',
                component: 'local_groups'
            }, {
                key: 'notdelconfirm',
                component: 'local_groups',
                param:name
            }]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    type: ModalFactory.types.DEFAULT,
                    body: s[1],
                }).done(function(modal) {
                    this.modal = modal;
                    modal.getRoot().find('[data-action="save"]').on('click', function() {
                        window.location.href = M.cfg.wwwroot +'/local/groups/edit.php?id='
                                                                +elem+'&confirm=1&delete=1&sesskey='
                                                                + M.cfg.sesskey;
                    }.bind(this));
                    modal.show();
                }.bind(this));
            }.bind(this));
        },
        hidecohort: function(elem, name) {
            return Str.get_strings([{
                key: 'hidecohort',
                component: 'local_groups'
            }, {
                key: 'hideconfirm',
                component: 'local_groups',
                param:name
            }]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    type: ModalFactory.types.DEFAULT,
                    body: s[1],
                    footer: '<button type="button" class="btn btn-primary" data-action="save">'
                                +M.util.get_string("yes", "moodle")+
                            '</button>&nbsp;' +
                            '<button type="button" class="btn btn-secondary" data-action="cancel">'
                                +M.util.get_string("no", "moodle")+
                            '</button>'
                }).done(function(modal) {
                    this.modal = modal;
                    modal.getRoot().find('[data-action="save"]').on('click', function() {
                        window.location.href =M.cfg.wwwroot +'/local/groups/edit.php?id='
                                                                +elem+'&confirm=1&hide=1&sesskey='
                                                                + M.cfg.sesskey;
                    }.bind(this));
                    modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                        modal.setBody('');
                        modal.hide();
                    });
                    modal.show();
                }.bind(this));
            }.bind(this));
        },

        unhidecohort: function(elem, name) {
            return Str.get_strings([{
                key: 'unhidecohort',
                component: 'local_groups'
            }, {
                key: 'unhideconfirm',
                component: 'local_groups',
                param:name
            }]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    type: ModalFactory.types.DEFAULT,
                    body: s[1],
                    footer: '<button type="button" class="btn btn-primary" data-action="save">'
                                +M.util.get_string("yes", "moodle")+
                            '</button>&nbsp;' +
                            '<button type="button" class="btn btn-secondary" data-action="cancel">'
                                +M.util.get_string("no", "moodle")+
                            '</button>'
                }).done(function(modal) {
                    this.modal = modal;
                    modal.getRoot().find('[data-action="save"]').on('click', function() {
                        window.location.reload();
                        window.location.href = M.cfg.wwwroot +'/local/groups/edit.php?id='
                                                                +elem+'&confirm=1&show=1&sesskey='
                                                                + M.cfg.sesskey;
                    }.bind(this));
                    modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                        modal.setBody('');
                        modal.hide();
                    });
                    modal.show();
                }.bind(this));
            }.bind(this));
        }
    };
});
