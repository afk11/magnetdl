### Magnet downloader

 This application has just one purpose: download torrents and sftp them to another machine.

### Install the application

 You can CLI tool globally for your user, using composers global feature. 
 
    composer global install afk11/magnetdl
 
 It can be upgraded using: `composer global update`
 
 NB: You need to add `~/.composer/bin` to your $PATH for the application to be found.

    echo "export PATH=~/.composer/vendor/bin:\$PATH" >> ~/.bashrc
    source ~/.bashrc # this is done automatically when you log in

#### Commands:
 - `magnetdl [magnet url]`: Starts upload procedure 
 - `magnetdlconfig`: Print a default config file to STDOUT

### Install as a library
 
 Add an entry to your composer.json, or run this in your proect directory: 
 
    compose require afk11/magnetdl
   
### Configuration:

You need to configure the software to run it. After installing globally run these:

    mkdir /home/you/.magnetdl
    magnetdlconfig > /home/you/.magnetdl/config.json
    nano /home/you/.magnetdl/config.json
   
Set up the config values and you're good to go!
   
### Setup Requirements

 You will need: 
  - your local machine, running transmission-daemon with the RPC configured.
  - remote machine with SSH (via keyfile authentication), with some folder you want to write to. 
   om   o
 Disclaimer: 
 The software expects an unencrypted private key as an identity file, 
 as we use `exec()` and can't provide an additional password.
 If you're worried about compromising the remote machine, the following 
 instructions should help lock things down. 
 
 1. Create a group for your user: `shared-jail`
 2. Create a new user: `youruser`, and add him to the `shared-jail` group: `usermod -a -G shared-jail youruser`
 3. Create an SSH key on your local system, and add the public key to the remote users `/home/youruser/.ssh/authorized_keys` file.
 4. Check you can login via SSH from the remote machine: `ssh youruser@yourhost -i /path/to/privatekey
 5. Create a basic chroot skeleton, and expose the folder you want to upload to using `mount --bind /sharedfiles /chroot/sharedfiles`. You may need a home directory, but it can be empty. 
 6. To auto-bind the shared files to the chroot on startup, add this to `/etc/fstab`. 
       
    /sharedfiles /chroot/sharedfiles none bind
    
    Be cautious with this one, errors can cause systems not to boot.
     
 7. Add this to the very bottom of your sshd_config:

    Match Group shared-jail
            ChrootDirectory %h
            ForceCommand internal-sftp
            AllowTcpForwarding no
 8. Before you log out from the remote system, do `service ssh restart`. 
 9. Try log in over SSH, you should now get: 'This service allows SFTP connections only.'
 10. Try log in over sftp: `sftp youruser@yourhost -oIdentityFile=/path/to/privatekey` and check you're in the chroot, and the folder is accessible.
 
 TLDR: Someone with the key can log into the chroot only, and can do anything with the files depending on file permissions.
 