$(document).ready(()=>{
    $.fn.modal.Constructor.prototype._enforceFocus = function() {};
    $(window).bind("beforeunload",function(event) {
        return "You have some unsaved changes";
    });
    let queueFiles = {};
    let progresses = $('#progresses');
    $("#drop-area").dmUploader({
        url: '/panel/upload',
        auto: true,
        queue: true,
        
        onNewFile(id, file) {
            let outer = $('<div/>').addClass('progress').addClass('my-2').css('height','18px');
            let bar = $('<div/>').addClass('progress-bar').attr('style','width: 0%').attr('role','progressbar').attr('aria-valuemin',"0").attr('aria-valuemax',"100").attr('aria-valuenow',"0");
            let p = $('<p/>').html(file.name + ' ');
            bar.append(p);
            outer.append(bar);
            progresses.append(outer);
            queueFiles[id] = {outer, bar, p, name: file.name};
        },

        onUploadProgress(id, p) {
            queueFiles[id].bar.attr('style',`width: ${p}%`).attr('aria-valuenow',p);
        },

        onUploadSuccess(id, data) {
            let getLink = $('<i tabindex="0" data-container="body" data-toggle="popover" data-placement="right" data-content="Link copied to clipboard" />')
                .addClass('material-icons').html('link').css({cursor: 'pointer', position: 'relative', top: '10px'});
            getLink.popover();
            getLink.on('click',()=>{
                copyToClipboard(window.location.protocol + "//" + window.location.host + "/static/uploaded/" + queueFiles[id].name);
                setTimeout(()=>{
                    let popover = $('#'+getLink.attr('aria-describedby'));
                    popover.fadeOut(300, ()=>{ popover.remove(); });
                }, 750);
            });
            queueFiles[id].bar.addClass('bg-success');
            queueFiles[id].p.append(getLink);
        },

        onUploadError(id, xhr, s, e) {
            queueFiles[id].bar.addClass('bg-danger').html('Error while uploading ' + queueFiles[id].name);
        },

    });	
});