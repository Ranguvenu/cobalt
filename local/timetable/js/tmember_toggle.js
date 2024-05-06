function teammember_list(teammanagerid) {
    YUI().use('node','transition', function(Y) {
	node = Y.one("#dialog"+teammanagerid+"");
        node.toggleView();
    });
}
