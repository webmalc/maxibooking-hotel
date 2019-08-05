function mbhSendParentPostMessage (action, data, target) {
    window.parent.postMessage({
        type: 'mbh',
        action: action,
        data: data || null,
        target: target || null
    }, "*");
}
