/*
* This file is a part of e abyas Info Solutions.
*
* Copyright eabyas Info Solutions Pvt Ltd, India.
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* @author e abyas  <info@eabyas.com>
*/
/**
 * Defines form autocomplete (types of form element)
 *
 * @package
 * @copyright  eabyas  <info@eabyas.com>
 */

define(['jquery', 'core/ajax', 'core/templates', 'core/str'], function($, Ajax, Templates, Str) {

    /** @var {Number} Maximum number of options to show. */
    var MAXOPTIONS = 100;

    return /** @alias module:enrol_manual/form-potential-option-selector */ {

        processResults: function(selector, results) {
            var options = [];
            if ($.isArray(results)) {
                $.each(results, function(index, option) {
                    options.push({
                        value: option.id,
                        label: option._label
                    });
                });
                return options;

            } else {
                return results;
            }
        },

        transport: function(selector, query, success, failure) {
            var promise;
            var contextid = parseInt($(selector).data('contextid'), 10);
            var action = $(selector).data('action');
            var pluginclass = $(selector).data('pluginclass');
            var formoptions = $(selector).data('options');
            var defaultstrings = Str.get_strings([
                {
                    key:'selectdept',
                    component: 'local_courses',
                },
                {
                    key:'selectsubdept',
                    component: 'local_courses',
                },
                {
                    key:'selectcat',
                    component: 'local_courses',
                },
                {
                    key:'select_batch',
                    component: 'local_program',
                },
                {
                    key:'select_curriculum_list',
                    component: 'local_program',
                },
                {
                    key:'selectcourse',
                    component: 'local_courses',
                }
                // {
                //     key:'selectcollege',
                //     component: 'local_costcenter',
                // }
            ]);
            defaultstrings.then(function(s){
                var departmentselect = '<span><span>'+s[0]+'</span></span>';
                var subdeptartmentselect = '<span><span>'+s[1]+'</span></span>';
                var categoryselect = '<span><span>'+s[2]+'</span></span>';
                var batchselect = '<span><span>'+s[3]+'</span></span>';
                var curriculumselect = '<span><span>'+s[4]+'</span></span>';
                var courseselect = '<span><span>'+s[5]+'</span></span>';
                // var collegeselect = '<span><span>'+s[6]+'</span></span>';

                if (action === 'costcenter_courseid_selector') {
                    formoptions.courseid = $("#id_coursesid").val();
                    $("#id_coursesid").on('change', function() {
                        var courseid = $("#id_coursesid").val();
                    });
                }
                if (action === 'costcenter_teacherid_selector') {
                    formoptions.teacherid = $("#id_teacherid").val();
                }
                if (action === 'costcenter_program_selector') {
                    formoptions.programid = $("#id_program").val();
                }
                if (action === 'program_level_selector') {
                    formoptions.levelid = $("#id_semester").val();
                }
                if (action === 'costcenter_building_selector') {
                    formoptions.building = $("#id_building").val();
                }
                if (action === 'costcenter_room_selector') {
                    formoptions.room = $("#id_room").val();
                }

                if (action === 'costcenter_departments_selector' ||
                    action === 'costcenter_subdepartment_selector' ||
                    action === 'costcenter_courses_selector') {
                    $("#id_open_departmentid").on('change', function() {
                        var subdept = $('#id_open_subdepartment').val();
                        if(parseInt(subdept) > 0){
                            $('#id_open_subdepartment').html('');
                            $('.subdepartmentselect .form-autocomplete-selection').html(subdeptartmentselect);
                        }
                        var courses = $('#id_course').val();
                        if(parseInt(courses) > 0){
                            $('#id_course').html('');
                            $('.courseselect .form-autocomplete-selection').html(courseselect);
                        }
                    });
                    formoptions.departmentid = $("#id_open_departmentid").val();
                    formoptions.subdepartment = $("#id_open_subdepartment").val();
                    formoptions.course = $("#id_course").val();
                }
                if (action === 'costcenter_organisation_selector') {
                    $("#id_open_costcenterid").on('change', function() {
                        var department = $('#id_open_departmentid').val();
                        // if(parseInt(department) > 0){
                        //     $('#id_open_departmentid').html('');
                        //     $('.departmentselect .form-autocomplete-selection').html(departmentselect);
                        // }
                        // var college = $('#id_open_collegeid').val();
                        // if(parseInt(college) > 0){
                        //     $('#id_open_collegeid').html('');
                        //     $('.collegeselect .form-autocomplete-selection').html(collegeselect);
                        // }
                        // var subdept = $('#id_open_subdepartment').val();

                        // if(parseInt(subdept) > 0){
                        //     $('#id_open_subdepartment').html('');
                        //     $('.subdepartmentselect .form-autocomplete-selection').html(subdeptartmentselect);
                        // }
                        var category = $('#id_category').val();
                        if(parseInt(category) > 0){
                            $('#id_category').html('');
                            $('.categoryselect .form-autocomplete-selection').html(categoryselect);
                        }
                        var batch = $('#id_batchid').val();
                        if(parseInt(batch) > 0){
                            $('#id_batchid').html('');
                            $('.batchselect .form-autocomplete-selection').html(batchselect);
                        }
                        var curriculum = $('#id_curriculumid').val();
                        if(parseInt(curriculum) > 0){
                            $('#id_curriculumid').html('');
                            $('.curriculumselect .form-autocomplete-selection').html(curriculumselect);
                        }
                        var courses = $('#id_course').val();
                        if(parseInt(courses) > 0){
                            $('#id_course').html('');
                            $('.courseselect .form-autocomplete-selection').html(courseselect);
                        }
                    });
                }else if(action === 'costcenter_department_selector'
                        || action === 'costcenter_subdepartment_selector'
                        || action === 'costcenter_batch_selector'
                        || action === 'costcenter_curriculum_selector'
                        || action === 'costcenter_courses_selector'){
                    $('#id_open_departmentid').on('change', function(){
                        var subdept = $('#id_open_subdepartment').val();
                        if(parseInt(subdept) > 0){
                            $('#id_open_subdepartment').html('');
                            $('.subdepartmentselect .form-autocomplete-selection').html(subdeptartmentselect);
                        }
                        var category = $('#id_category').val();
                        if(parseInt(category) > 0){
                            $('#id_category').html('');
                            $('.categoryselect .form-autocomplete-selection').html(categoryselect);
                        }
                        var batch = $('#id_batchid').val();
                        if(parseInt(batch) > 0){
                            $('#id_batchid').html('');
                            $('.batchselect .form-autocomplete-selection').html(batchselect);
                        }
                        var curriculum = $('#id_curriculumid').val();
                        if(parseInt(curriculum) > 0){
                            $('#id_curriculumid').html('');
                            $('.curriculumselect .form-autocomplete-selection').html(curriculumselect);
                        }
                        var courses = $('#id_course').val();
                        if(parseInt(courses) > 0){
                            $('#id_course').html('');
                            $('.courseselect .form-autocomplete-selection').html(courseselect);
                        }
                    });
                    $('#id_open_subdepartment').on('change', function(){
                        var category = $('#id_category').val();
                        if(parseInt(category) > 0){
                            $('#id_category').html('');
                            $('.categoryselect .form-autocomplete-selection').html(categoryselect);
                        }
                        var batch = $('#id_batchid').val();
                        if(parseInt(batch) > 0){
                            $('#id_batchid').html('');
                            $('.batchselect .form-autocomplete-selection').html(batchselect);
                        }
                        var curriculum = $('#id_curriculumid').val();
                        if(parseInt(curriculum) > 0){
                            $('#id_curriculumid').html('');
                            $('.curriculumselect .form-autocomplete-selection').html(curriculumselect);
                        }
                        var courses = $('#id_course').val();
                        if(parseInt(courses) > 0){
                            $('#id_course').html('');
                            $('.courseselect .form-autocomplete-selection').html(courseselect);
                        }
                    });
                }
            });
            if(action === 'costcenter_department_selector' ||
                // action === 'costcenter_college_selector' ||
                action === 'costcenter_subdepartment_selector' ||
                action === 'costcenter_teacherid_selector' ||
                // action === 'costcenter_program_selector' ||
                action === 'costcenter_organisation_selector' ||
                action === 'costcenter_role_selector' ||
                action === 'program_level_selector'||
                action === 'costcenter_room_selector'){
                formoptions.roleid = $("#id_roleid").val();
                // formoptions.flag = true;
                formoptions.parentid = $('[data-class="' + $(selector).data('parentclass') + '"]').val();
            }else if(action === 'costcenter_category_selector'
                || action === 'costcenter_batch_selector'
                || action === 'costcenter_curriculum_selector'
                || action === 'costcenter_courses_selector'
                || action === 'costcenter_program_selector'
                || action === 'costcenter_role_selector'
                || action === 'costcenter_element_selector'){
                var parentid = $('[data-class="' + $(selector).data('parentclass') + '"]').val();
                if(!(parentid == undefined && formoptions.parentid > 0)){
                    formoptions.parentid = parentid;
                }
                formoptions.organisationid = $("#id_open_costcenterid").val();
                formoptions.roleid = $("#id_roleid").val();
                formoptions.departmentid = $("#id_open_departmentid").val();
                formoptions.subdepartment = $("#id_open_subdepartment").val();
                formoptions.batchid = $("#id_batchid").val();
                formoptions.curriculumid = $("#id_curriculumid").val();
                formoptions.course = $("#id_course").val();
            }
            formoptions = JSON.stringify(formoptions);
            // console.log(formoptions);
            promise = Ajax.call([{
                methodname: 'local_costcenter_form_option_selector',
                args: {
                    query: query,
                    context: {contextid: contextid},
                    action: action,
                    options: formoptions,
                    searchanywhere: true,
                    page: 0,
                    perpage: MAXOPTIONS + 1,
                    pluginclass: pluginclass,
                }
            }]);

            promise[0].then(function(results) {
                results = $.parseJSON(results);
                var promises = [],
                    i = 0;
                var contexttemplate;
                    contexttemplate = 'local_costcenter/form-option-selector-suggestion';
                if (results.length <= MAXOPTIONS) {
                    // Render the label.
                    $.each(results, function(index, option) {
                        $("#id_roleid").on('change', function() {
                            var rolecost = $("#id_roleid").val();
                                if(rolecost == option.id) {
                                    if(option.shortname == 'orgadmin'){
                                        $("#fitem_id_open_departmentid").hide();
                                        $("#fitem_id_open_subdepartment").hide();
                                    } else {
                                        $("#fitem_id_open_departmentid").show();
                                        $("#fitem_id_open_subdepartment").show();
                                    }
                        //         if (option.dept_or_col == 1) {
                        //             $('#id_open_departmentid').html('');
                        //             $('.departmentselect .d-inline').html($('#id_open_departmentid').data('clgstring'));
                        //             $('.departmentselect .form-autocomplete-selection').html($('#id_open_departmentid').data('clgstring'));
                                    
                        //             $('#id_open_subdepartment').html('');
                        //             $('.subdepartmentselect .d-inline').html($('#id_open_subdepartment').data('clgdeptstring'));
                        //         } else if (option.dept_or_col == 0) {
                        //             $('#id_open_departmentid').html('');
                        //             $('.departmentselect .d-inline').html($('#id_open_departmentid').data('deptstring'));
                                    
                        //             $('#id_open_subdepartment').html('');
                        //             $('.subdepartmentselect .d-inline').html($('#id_open_subdepartment').data('subdeptstring'));
                        //         }
                            }
                        });
                        var ctx = option,
                            identity = [];
                            ctx.hasidentity = true;
                        ctx.identity = identity.join(', ');
                        promises.push(Templates.render(contexttemplate, ctx));
                    });

                    // Apply the label to the results.
                    return $.when.apply($.when, promises).then(function() {
                        var args = arguments;
                        $.each(results, function(index, option) {
                            option._label = args[i];
                            i++;
                        });
                        success(results);
                        return;
                    });

                } else {
                    return Str.get_string('toomanyoptionstoshow', 'local_costcenter', '>' + MAXOPTIONS)
                        .then(function(toomanyoptionstoshow) {
                            success(toomanyoptionstoshow);
                            return;
                        });
                }

            }).fail(failure);
        }

    };

});
