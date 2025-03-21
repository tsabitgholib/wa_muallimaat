/*!
 * Tabledit v1.2.3 (https://github.com/markcell/jQuery-Tabledit)
 * Copyright (c) 2015 Celso Marques
 * Licensed under MIT (https://github.com/markcell/jQuery-Tabledit/blob/master/LICENSE)
 */
if ("undefined" == typeof jQuery) throw new Error("Tabledit requires jQuery library.");
! function (t) {
   "use strict";
   t.fn.Tabledit = function (e) {
      function n(e) {
         var n = i.find(".tabledit-input").serialize() + "&action=" + e,
            a = d.onAjax(e, n);
         if (a === !1) return !1;
         var l = t.post(d.url, n, function (t, n, a) {
            e === d.buttons.edit.action && (s.removeClass(d.dangerClass).addClass(d.warningClass), setTimeout(function () {
               i.find("tr." + d.warningClass).removeClass(d.warningClass)
            }, 1400)), d.onSuccess(t, n, a)
         }, "json");
         return l.fail(function (t, n, i) {
            e === d.buttons["delete"].action ? (o.removeClass(d.mutedClass).addClass(d.dangerClass), o.find(".tabledit-toolbar button").attr("disabled", !1), o.find(".tabledit-toolbar .tabledit-restore-button").hide()) : e === d.buttons.edit.action && s.addClass(d.dangerClass), d.onFail(t, n, i)
         }), l.always(function () {
            d.onAlways()
         }), l
      }
      if (!this.is("table")) throw new Error("Tabledit only works when applied to a table.");
      var i = this,
         a = {
            url: window.location.href,
            inputClass: "form-control input-sm",
            toolbarClass: "btn-toolbar",
            groupClass: "btn-group btn-group-sm",
            dangerClass: "danger",
            warningClass: "warning",
            mutedClass: "text-muted",
            eventType: "click",
            rowIdentifier: "id",
            hideIdentifier: !1,
            autoFocus: !0,
            editButton: !0,
            deleteButton: !0,
            saveButton: !0,
            restoreButton: !0,
            buttons: {
               edit: {
                  "class": "btn btn-sm btn-default",
                  html: '<span>Edit</span>',
                  action: "edit"
               },
               "delete": {
                  "class": "btn btn-sm btn-default",
                  html: '<span class="glyphicon glyphicon-trash"></span>',
                  action: "delete"
               },
               save: {
                  "class": "btn btn-sm btn-success",
                  html: "Save"
               },
               restore: {
                  "class": "btn btn-sm btn-warning",
                  html: "Restore",
                  action: "restore"
               },
               confirm: {
                  "class": "btn btn-sm btn-danger",
                  html: "Confirm"
               }
            },
            onDraw: function () {},
            onSuccess: function () {
            },
            onFail: function () {},
            onAlways: function () {},
            onAjax: function () {}
         },
         d = t.extend(!0, a, e),
         s = "undefined",
         o = "undefined",
         l = "undefined",
         r = {
            columns: {
               identifier: function () {
                  d.hideIdentifier && i.find("th:nth-child(" + parseInt(d.columns.identifier[0]) + "1), tbody td:nth-child(" + parseInt(d.columns.identifier[0]) + "1)").hide();
                  var e = i.find("tbody td:nth-child(" + (parseInt(d.columns.identifier[0]) + 1) + ")");
                  e.each(function () {
                     var e = '<span class="tabledit-span tabledit-identifier">' + t(this).text() + "</span>",
                        n = '<input class="tabledit-input tabledit-identifier" type="hidden" name="' + d.columns.identifier[1] + '" value="' + t(this).text() + '" disabled>';
                     t(this).html(e + n), t(this).parent("tr").attr(d.rowIdentifier, t(this).text())
                  })
               },
               editable: function () {
                  for (var e = 0; e < d.columns.editable.length; e++) {
                     var n = i.find("tbody td:nth-child(" + (parseInt(d.columns.editable[e][0]) + 1) + ")");
                     n.each(function () {
                        var n = t(this).text();
                        d.editButton || t(this).css("cursor", "pointer");
                        var i = '<span class="tabledit-span">' + n + "</span>";
                        if ("undefined" != typeof d.columns.editable[e][2]) {
                           var a = '<select class="tabledit-input ' + d.inputClass + '" name="' + d.columns.editable[e][1] + '" style="display: none;" disabled>';
                           t.each(jQuery.parseJSON(d.columns.editable[e][2]), function (t, e) {
                              a += n === e ? '<option value="' + t + '" selected>' + e + "</option>" : '<option value="' + t + '">' + e + "</option>"
                           }), a += "</select>"
                        } else var a = '<input class="tabledit-input ' + d.inputClass + '" type="text" name="' + d.columns.editable[e][1] + '" value="' + t(this).text() + '" style="display: none;" disabled>';
                        t(this).html(i + a), t(this).addClass("tabledit-view-mode")
                     })
                  }
               },
               toolbar: function () {
                  if (d.editButton || d.deleteButton) {
                     var t = "",
                        e = "",
                        n = "",
                        a = "",
                        s = "";
                     0 === i.find("th.tabledit-toolbar-column").length && i.find("tr:first").append('<th class="tabledit-toolbar-column"></th>'), d.editButton && (t = '<button type="button" class="tabledit-edit-button ' + d.buttons.edit["class"] + '" style="float: none;">' + d.buttons.edit.html + "</button>"), d.deleteButton && (e = '<button type="button" class="tabledit-delete-button ' + d.buttons["delete"]["class"] + '" style="float: none;">' + d.buttons["delete"].html + "</button>", s = '<button type="button" class="tabledit-confirm-button ' + d.buttons.confirm["class"] + '" style="display: none; float: none;">' + d.buttons.confirm.html + "</button>"), d.editButton && d.saveButton && (n = '<button type="button" class="tabledit-save-button ' + d.buttons.save["class"] + '" style="display: none; float: none;">' + d.buttons.save.html + "</button>"), d.deleteButton && d.restoreButton && (a = '<button type="button" class="tabledit-restore-button ' + d.buttons.restore["class"] + '" style="display: none; float: none;">' + d.buttons.restore.html + "</button>");
                     var o = '<div class="tabledit-toolbar ' + d.toolbarClass + '" style="text-align: left;">\n                                           <div class="' + d.groupClass + '" style="float: none;">' + t + e + "</div>\n                                           " + n + "\n                                           " + s + "\n                                           " + a + "\n                                       </div></div>";
                     i.find("tr:gt(0)").append('<td style="white-space: nowrap; width: 1%;">' + o + "</td>")
                  }
               }
            }
         },
         u = {
            view: function (e) {
               var n = t(e).parent("tr");
               t(e).parent("tr").find(".tabledit-input.tabledit-identifier").prop("disabled", !0), t(e).find(".tabledit-input").blur().hide().prop("disabled", !0), t(e).find(".tabledit-span").show(), t(e).addClass("tabledit-view-mode").removeClass("tabledit-edit-mode"), d.editButton && (n.find("button.tabledit-save-button").hide(), n.find("button.tabledit-edit-button").removeClass("active").blur())
            },
            edit: function (e) {
               c.reset(e);
               var n = t(e).parent("tr");
               n.find(".tabledit-input.tabledit-identifier").prop("disabled", !1), t(e).find(".tabledit-span").hide();
               var i = t(e).find(".tabledit-input");
               i.prop("disabled", !1).show(), d.autoFocus && i.focus(), t(e).addClass("tabledit-edit-mode").removeClass("tabledit-view-mode"), d.editButton && (n.find("button.tabledit-edit-button").addClass("active"), n.find("button.tabledit-save-button").show())
            }
         },
         b = {
            reset: function (e) {
               t(e).each(function () {
                  var e = t(this).find(".tabledit-input"),
                     n = t(this).find(".tabledit-span").text();
                  e.is("select") ? e.find("option").filter(function () {
                     return t.trim(t(this).text()) === n
                  }).attr("selected", !0) : e.val(n), u.view(this)
               })
            },
            submit: function (e) {
               var i = n(d.buttons.edit.action);
               i !== !1 && (t(e).each(function () {
                  var e = t(this).find(".tabledit-input");
                  t(this).find(".tabledit-span").text(e.is("select") ? e.find("option:selected").text() : e.val()), u.view(this)
               }), s = t(e).parent("tr"))
            }
         },
         c = {
            reset: function (t) {
               i.find(".tabledit-confirm-button").hide(), i.find(".tabledit-delete-button").removeClass("active").blur()
            },
            submit: function (e) {
               c.reset(e), t(e).parent("tr").find("input.tabledit-identifier").attr("disabled", !1);
               var i = n(d.buttons["delete"].action);
               t(e).parents("tr").find("input.tabledit-identifier").attr("disabled", !0), i !== !1 && (t(e).parent("tr").addClass("tabledit-deleted-row"), t(e).parent("tr").addClass(d.mutedClass).find(".tabledit-toolbar button:not(.tabledit-restore-button)").attr("disabled", !0), t(e).find(".tabledit-restore-button").show(), o = t(e).parent("tr"))
            },
            confirm: function (e) {
               i.find("td.tabledit-edit-mode").each(function () {
                  b.reset(this)
               }), t(e).find(".tabledit-delete-button").addClass("active"), t(e).find(".tabledit-confirm-button").show()
            },
            restore: function (e) {
               t(e).parent("tr").find("input.tabledit-identifier").attr("disabled", !1);
               var i = n(d.buttons.restore.action);
               t(e).parents("tr").find("input.tabledit-identifier").attr("disabled", !0), i !== !1 && (t(e).parent("tr").removeClass("tabledit-deleted-row"), t(e).parent("tr").removeClass(d.mutedClass).find(".tabledit-toolbar button").attr("disabled", !1), t(e).find(".tabledit-restore-button").hide(), l = t(e).parent("tr"))
            }
         };
      return r.columns.identifier(), r.columns.editable(), r.columns.toolbar(), d.onDraw(), d.deleteButton && (i.on("click", "button.tabledit-delete-button", function (e) {
         if (e.handled !== !0) {
            e.preventDefault();
            var n = t(this).hasClass("active"),
               i = t(this).parents("td");
            c.reset(i), n || c.confirm(i), e.handled = !0
         }
      }), i.on("click", "button.tabledit-confirm-button", function (e) {
         if (e.handled !== !0) {
            e.preventDefault();
            var n = t(this).parents("td");
            c.submit(n), e.handled = !0
         }
      })), d.restoreButton && i.on("click", "button.tabledit-restore-button", function (e) {
         e.handled !== !0 && (e.preventDefault(), c.restore(t(this).parents("td")), e.handled = !0)
      }), d.editButton ? (i.on("click", "button.tabledit-edit-button", function (e) {
         if (e.handled !== !0) {
            e.preventDefault();
            var n = t(this),
               a = n.hasClass("active");
            b.reset(i.find("td.tabledit-edit-mode")), a || t(n.parents("tr").find("td.tabledit-view-mode").get().reverse()).each(function () {
               u.edit(this)
            }), e.handled = !0
         }
      }), i.on("click", "button.tabledit-save-button", function (e) {
         e.handled !== !0 && (e.preventDefault(), b.submit(t(this).parents("tr").find("td.tabledit-edit-mode")), e.handled = !0)
      })) : (i.on(d.eventType, "tr:not(.tabledit-deleted-row) td.tabledit-view-mode", function (t) {
         t.handled !== !0 && (t.preventDefault(), b.reset(i.find("td.tabledit-edit-mode")), u.edit(this), t.handled = !0)
      }), i.on("change", "select.tabledit-input:visible", function () {
         event.handled !== !0 && (b.submit(t(this).parent("td")), event.handled = !0)
      }), t(document).on("click", function (t) {
         var e = i.find(".tabledit-edit-mode");
         e.is(t.target) || 0 !== e.has(t.target).length || b.reset(i.find(".tabledit-input:visible").parent("td"))
      })), t(document).on("keyup", function (t) {
         var e = i.find(".tabledit-input:visible"),
            n = i.find(".tabledit-confirm-button");
         if (e.length > 0) var a = e.parents("td");
         else {
            if (!(n.length > 0)) return;
            var a = n.parents("td")
         }
         switch (t.keyCode) {
            case 9:
               d.editButton || (b.submit(a), u.edit(a.closest("td").next()));
               break;
            case 13:
               b.submit(a);
               break;
            case 27:
               b.reset(a), c.reset(a)
         }
      }), this
   }
}(jQuery);