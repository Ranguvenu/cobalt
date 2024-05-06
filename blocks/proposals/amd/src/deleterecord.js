// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Add a create new group modal to the page.
 *
 * @module     block_proposals/deleterecord
 * @class      deleterecord
 * @package    block_proposals
 * @copyright  2017 Damyon Wiese <damyon@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/fragment', 'core/ajax', 'core/yui', 'core/templates', 'core/notification'],
        function($, Str, ModalFactory, ModalEvents, Fragment, Ajax, Y, Templates, Notification) {
          var deleterecord = function(args) {
                this.contextid = args.contextid || 1;
                this.args = args;
                var self=this;
                self.init(args);
          };
        /**
         * @var {Modal} modal
         * @private
         */
        deleterecord.prototype.modal = null;
        /**
         * @var {int} contextid
         * @private
         */
        deleterecord.prototype.contextid = -1;
           /**
     * Initialise the class.
     *
     * @param {String} selector used to find triggers for the new group modal.
     * @private
     * @return {Promise}
     */
 
     return  {
        init: function(args) {
           return new deleterecord(args);
        },

        deleteConfirm: function(args){
            return Str.get_strings([{
                key: 'confirm',
                component: 'block_proposals',
            },
            {
                key: 'deleteconfirm',
                component: 'block_proposals',
                param : args
            },
            {
                key: 'delete'
            }
          ]).then(function(s) {
                ModalFactory.create({
                    title: s[0],
                    type: ModalFactory.types.DEFAULT,
                    body: s[1],
                    footer: '<button type="button" class="btn btn-primary" data-action="save">Yes! Delete</button>&nbsp;' +
            '<button type="button" class="btn btn-danger" data-action="cancel">Cancel</button>'
                }).done(function(modal) {
                    this.modal = modal;
                    modal.getRoot().find('[data-action="save"]').on('click', function() {
                        args.confirm = true;
                        var promise = Ajax.call([{
                            methodname: 'block_proposal_' + args.action,
                            args: {
                                id: args.id
                            },
                        }]);
                        promise[0].done(function() {
                            window.location.reload();
                            window.location.href = window.location.href;
                        }).fail(function(ex) {
                            // do something with the exception
                             console.log(ex);
                        });
                    }.bind(this));
                    modal.getFooter().find('[data-action="cancel"]').on('click', function() {
                        modal.setBody('');
                        modal.hide();
                    });
                    modal.show();
                }.bind(this));
            }.bind(this));
        },
       load:function () {}

    };

});
