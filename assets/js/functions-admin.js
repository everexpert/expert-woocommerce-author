(function ($) {
  "use strict";

  var media_uploader = null;

  function open_media_uploader_image(event, imageSelectorButton) {

      var $imageSelectorScope = imageSelectorButton.parent();

      media_uploader = wp.media({
          frame: "post",
          state: "insert",
          multiple: false
      });

      media_uploader.on("insert", function () {
          var json = media_uploader.state().get("selection").first().toJSON();
          var image_id = json.id;
          var image_url = json.url;
          var image_html = '<img src="' + image_url + '" width="90" height="90">';

          var current_selector = '';
          switch (event.target.id) {
              case 'ewa_author_image_select':
                  current_selector = '.taxonomy-ewa-author #ewa_author_' + 'image';
                  break;
              case 'ewa_author_banner_select':
                  current_selector = '.taxonomy-ewa-author #ewa_author_' + 'banner';
                  break;
          }

          $(current_selector).val(image_id);
          $(current_selector + '_result').remove();

          if ($('.ewa_author_image_selected', $imageSelectorScope).length) {
              $('.ewa_author_image_selected span', $imageSelectorScope).html(image_html);
          } else {
              $imageSelectorScope.append('<div class="ewa_author_image_selected"><span>' + image_html + '</span></div>');
          }
          add_delete_link($imageSelectorScope);

      });

      media_uploader.open();
  }


  $('.taxonomy-ewa-author #ewa_author_image_select, .taxonomy-ewa-author #ewa_author_banner_select').on('click', function (event) {
      open_media_uploader_image(event, $(this));
  });

  //bind remove image event for edit page
  $('.taxonomy-ewa-author #ewa_author_image_select, .taxonomy-ewa-author #ewa_author_banner_select').each(function () {
      add_delete_link($(this).parent());
  });

  //clear custom fields when author is added
  if ($('body').hasClass('edit-tags-php') && $('body').hasClass('taxonomy-ewa-author')) {
      $(document).ajaxSuccess(function (event, xhr, settings) {
          //Check ajax action of request that succeeded
          if (typeof settings != "undefined" && settings.data && ~settings.data.indexOf("action=add-tag") && ~settings.data.indexOf("taxonomy=ewa-author")) {
              $('#ewa_author_image').val('');
              $('#ewa_author_banner').val('');
              $('.ewa_author_image_selected').remove();
          }
      });
  }

  function add_delete_link($imageSelectorScope) {

      $('.ewa_author_image_selected span', $imageSelectorScope).append('<a href="#" class="ewa_author_image_selected_remove">X</a>');

      $('.ewa_author_image_selected_remove', $imageSelectorScope).on('click', function (event) {

          event.preventDefault();
          $(this).closest('.ewa_author_image_selected').remove();

          //remove the img
          $('#ewa_author_image', $imageSelectorScope).val('');
          $('#ewa_author_banner', $imageSelectorScope).val('');

      });

  }

  /* ····························· Edit authors page ····························· */
  $('.taxonomy-ewa-author table .column-featured > span').not('ewa-blocked').on('click', function (e) {
      e.preventDefault();
      var $currentStar = $(this);
      $currentStar.addClass('ewa-blocked');
      if ($currentStar.hasClass('dashicons-star-filled')) {
          $currentStar.removeClass('dashicons-star-filled');
          $currentStar.addClass('dashicons-star-empty');
      } else {
          $currentStar.removeClass('dashicons-star-empty');
          $currentStar.addClass('dashicons-star-filled');
      }
      var data = { 'action': 'ewa_admin_set_featured_author', 'author': $currentStar.data('author-id') };
      $.post(ewa_ajax_object_admin.ajax_url, data, function (response) {
          $currentStar.removeClass('ewa-blocked');
          if (response.success) {
              var $featuredCount = $('.taxonomy-ewa-author .ewa-featured-count > span');
              if (response.data.direction == 'up') {
                  $featuredCount.html(parseInt($featuredCount.text()) + 1);
              } else {
                  $featuredCount.html(parseInt($featuredCount.text()) - 1);
              }
          } else {
              alert(response.data.error_msg);
          }
      });
  });

  $('.taxonomy-ewa-author #ewa-first-featured-authors').on('change', function (e) {
      e.preventDefault();
      $('#screen-options-apply').replaceWith('<img src="' + ewa_ajax_object_admin.site_url + '/wp-admin/images/loading.gif">');
      var data = { 'action': 'ewa_admin_save_screen_settings', 'new_val': $(this).is(':checked') };
      $.post(ewa_ajax_object_admin.ajax_url, data, function (response) { location.reload(); });
  });

  $('.ewa-edit-authors-bottom > span').on('click', function (e) {
      e.preventDefault();
      $('.taxonomy-ewa-author #col-left').toggleClass('ewa-force-full-width');
      $('.taxonomy-ewa-author #col-right').toggleClass('ewa-force-full-width');
  });
  /* ····························· /Edit authors page ····························· */

  /* ····························· Settings tab ····························· */

  if ($('.ewa-admin-selectwoo').length) {
      $('.ewa-admin-selectwoo').selectWoo();
  }

  // migrate authors
  $('#wc_ewa_admin_tab_tools_migrate').on('change', function () {

      if ($(this).val() != '-') {

          if (confirm(ewa_ajax_object_admin.translations.migrate_notice)) {

              $('html').append('<div class="ewa-modal"><div class="ewa-modal-inner"></div></div>');
              $('.ewa-modal-inner').html('<p>' + ewa_ajax_object_admin.translations.migrating + '</p>');

              var data = {
                  'action': 'ewa_admin_migrate_authors',
                  'from': $(this).val()
              };
              $.post(ewa_ajax_object_admin.ajax_url, data, function (response) {

                  setTimeout(function () {
                      location.href = ewa_ajax_object_admin.authors_url;
                  }, 1000);

              });

          } else {

          }

      }

      $(this).val('-');//reset to default value

  });

  // dummy data
  $('#wc_ewa_admin_tab_tools_dummy_data').on('change', function () {

      if ($(this).val() != '-') {

          if (confirm(ewa_ajax_object_admin.translations.dummy_data_notice)) {

              $('html').append('<div class="ewa-modal"><div class="ewa-modal-inner"></div></div>');
              $('.ewa-modal-inner').html('<p>' + ewa_ajax_object_admin.translations.dummy_data + '</p>');

              var data = {
                  'action': 'ewa_admin_dummy_data',
                  'from': $(this).val()
              };
              $.post(ewa_ajax_object_admin.ajax_url, data, function (response) {

                  setTimeout(function () {
                      location.href = ewa_ajax_object_admin.authors_url;
                  }, 1000);

              });

          } else {

          }

      }

      $(this).val('-');//reset to default value

  });

  var $systemStatusBtn = $('#wc_ewa_admin_tab_tools_system_status').siblings('p');
  $systemStatusBtn.addClass('button wc_ewa_admin_tab_status_btn');
  $('.wc_ewa_admin_tab_status_btn').on('click', function (e) {
      e.preventDefault();
      if (!$('#wc_ewa_admin_status_result').length) {
          var $systemStatusTextarea = $('#wc_ewa_admin_tab_tools_system_status');
          $('<pre id="wc_ewa_admin_status_result"></pre>').insertAfter($systemStatusTextarea);
          $('#wc_ewa_admin_status_result').click(function (e) {
              e.preventDefault();
              var refNode = $(this)[0];
              if ($.browser.msie) {
                  var range = document.body.createTextRange();
                  range.moveToElementText(refNode);
                  range.select();
              } else if ($.browser.mozilla || $.browser.opera) {
                  var selection = window.getSelection();
                  var range = document.createRange();
                  range.selectNodeContents(refNode);
                  selection.removeAllRanges();
                  selection.addRange(range);
              } else if ($.browser.safari) {
                  var selection = window.getSelection();
                  selection.setBaseAndExtent(refNode, 0, refNode, 1);
              }
          });
      }
      $('#wc_ewa_admin_status_result').html('<img src="' + ewa_ajax_object_admin.site_url + '/wp-admin/images/spinner.gif' + '" alt="Loading" height="20" width="20">');
      $('#wc_ewa_admin_status_result').show();
      var data = {
          'action': 'ewa_system_status'
      };
      $.post(ajaxurl, data, function (response) {
          $('#wc_ewa_admin_status_result').html(response);
          $('#wc_ewa_admin_status_result').trigger('click');
      });

  });

  /* ····························· /Settings tab ····························· */

  /* ····························· Admin notices ····························· */
  $(document).on('click', '.ewa-notice-dismissible .notice-dismiss', function (e) {

      e.preventDefault();

      var noticeName = $(this).closest('.ewa-notice-dismissible').data('notice');

      var data = {
          'action': 'dismiss_ewa_notice',
          'notice_name': noticeName
      };
      $.post(ajaxurl, data, function (response) {
          //callback
      });

  });
  /* ····························· /Admin notices ····························· */

  /* ····························· Widgets ····························· */
  ewaBindEventsToWigets();
  //Fires when a widget is added to a sidebar
  $(document).bind('widget-added', function (e, widget) {
      ewaBindEventsToWigets(widget);
  });
  //Fires on widget save
  $(document).on('widget-updated', function (e, widget) {
      ewaBindEventsToWigets(widget);
  });
  function ewaBindEventsToWigets(widget) {

      var $currentWidget = $(".ewa-select-display-as");

      if (widget != undefined) {
          $currentWidget = $(".ewa-select-display-as", widget);
      }
      $currentWidget.on("change", function () {
          if ($(this).val() == "author_logo") {
              $(this).parent().siblings(".ewa-display-as-logo").addClass("show");
          } else {
              $(this).parent().siblings(".ewa-display-as-logo").removeClass("show");
          }
      });
  }
  /* ····························· /Widgets ····························· */

  /* ····························· Authors exporter ····························· */
  $('button.ewa-authors-export').on('click', function (e) {
      e.preventDefault();

      var $clickedBtn = $(this);
      $clickedBtn.addClass('ewa-loading-overlay');
      $clickedBtn.prop("disabled", true);

      var data = { 'action': 'ewa_authors_export' };
      $.post(ewa_ajax_object_admin.ajax_url, data, function (response) {

          if (response.success) {
              $clickedBtn.removeClass('ewa-loading-overlay');
              $clickedBtn.prop("disabled", false);

              //download export file
              $('#ewa-download-export-file').remove();
              var link = document.createElement("a");
              link.download = 'authors.json';
              link.id = 'ewa-download-export-file';
              link.href = response.data.export_file_url;
              $('body').append(link);
              link.click();
          }

      });

  })

  $('button.ewa-authors-import').on('click', function (e) {
      e.preventDefault();
      $('input.ewa-authors-import-file').trigger('click');
  });

  $('input.ewa-authors-import-file').on('change', function (e) {
      e.preventDefault();

      var $clickedBtn = $('button.ewa-authors-import');
      $clickedBtn.addClass('ewa-loading-overlay');
      $clickedBtn.prop("disabled", true);

      var file = $(this)[0].files[0];

      var reqData = new FormData();
      reqData.append('action', 'ewa_authors_import');
      reqData.append('file', file);

      $.ajax({
          url: ewa_ajax_object_admin.ajax_url,
          type: 'post',
          cache: false,
          dataType: 'json',
          contentType: false,
          processData: false,
          data: reqData,
          success: function (resp) {
              if (resp.success) {
                  $clickedBtn.removeClass('ewa-loading-overlay');
                  location.reload();
              } else {
                  alert('Importer error');
              }
          }
      });

  })
  
  /* ····························· /Authors exporter ····························· */

})(jQuery)
