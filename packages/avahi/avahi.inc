<?php

function setup_avahi() {
	global $g, $config;

	// Pull some various values out of config.xml
	$hostname = $config['system']['hostname'];
	$domain = $config['system']['domain'];
	$enable = $config['installedpackages']['avahi']['config'][0]['enable'];
	$browsedomains = $config['installedpackages']['avahi']['config'][0]['browsedomains'];

	// Is package disabled?
	if(!$enable) {
		if(file_exists("/usr/local/etc/rc.d/avahi.sh"))	
			unlink("/usr/local/etc/rc.d/avahi.sh");
		return;
	}

	// No supplied domains?  Use the defaults.
	if(!$browsedomains)
		$browsedomains = "local, 0pointer.de, zeroconf.org";

	// Construct the avahi configuration
	$config   = <<<EOF

	# avahi.conf - This file was automatically generated by the pfSense pacakge
	# manager.  Do not edit this file, it will be overwritten automatically. 
	# See /usr/local/pkg/avahi.inc to make changes to this file!

    [server]
    host-name={$hostname}
    domain-name={$domain}
    browse-domains={$browsedomains}
    use-ipv4=yes
    use-ipv6=no
    enable-dbus=no
    #check-response-ttl=no
    #use-iff-running=no
    #disallow-other-stacks=no
    #allow-point-to-point=no

    [wide-area]
    enable-wide-area=yes

    [publish]
    #disable-publishing=no
    #disable-user-service-publishing=no
    #add-service-cookie=no
    #publish-addresses=yes
    #publish-hinfo=yes
    #publish-workstation=yes
    #publish-domain=yes
    #publish-dns-servers=192.168.50.1, 192.168.50.2
    #publish-resolv-conf-dns-servers=yes
    #publish-aaaa-on-ipv4=yes
    #publish-a-on-ipv6=no

    [reflector]
    enable-reflector=yes
    #reflect-ipv=no

    [rlimits]
    rlimit-core=0
    rlimit-data=4194304
    rlimit-fsize=0
    rlimit-nofile=300
    rlimit-stack=4194304
    rlimit-nproc=3
    #rlimit-as=

EOF;

	// Make image RW
	conf_mount_rw();
	/* Write out .conf file */
	$fd = fopen("/usr/local/etc/avahi.conf", "w");
	fwrite($fd, $config);
	fclose($fd);
	/* Write out rc.d startup file */
	$start = "/usr/local/sbin/avahi";
	$stop = "/usr/bin/killall avahi";
	write_rcfile(array(
		"file" => "avahi.sh",
		"start" => $start,
		"stop" =>  $stop
		)
	);
	// Make image RO
	conf_mount_ro();

}

?>