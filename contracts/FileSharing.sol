// SPDX-License-Identifier: MIT
pragma solidity ^0.8.19;

contract FileSharing {
    struct File {
        string ipfsHash;
        string fileName;
        uint256 timestamp;
        address owner;
        bool isPublic;
        string description;
        string fileType;
        uint256 fileSize;
        bool isDeleted;  // New field to mark files as deleted
    }

    struct SharedFile {
        uint256 fileIndex;
        address sharedBy;
        uint256 sharedAt;
        bool hasAccess;
    }

    // Mapping from user address to their files
    mapping(address => File[]) private userFiles;
    
    // Mapping from user address to their shared files
    mapping(address => SharedFile[]) private sharedFiles;

    // New mapping to track recipients for each file
    mapping(address => mapping(uint256 => address[])) private fileRecipients;
    mapping(address => mapping(uint256 => mapping(address => bool))) private recipientAccess;

    // Events
    event FileUploaded(
        address indexed owner,
        string ipfsHash,
        string fileName,
        uint256 timestamp,
        bool isPublic
    );

    event FileShared(
        address indexed sharedBy,
        address indexed sharedWith,
        uint256 fileIndex,
        uint256 timestamp
    );

    event FileAccessRevoked(
        address indexed revokedBy,
        address indexed revokedFrom,
        uint256 fileIndex,
        uint256 timestamp
    );

    event FileAccessGranted(
        address indexed grantedBy,
        address indexed grantedTo,
        uint256 fileIndex,
        uint256 timestamp
    );

    event FileDeleted(
        address indexed owner,
        uint256 fileIndex,
        uint256 timestamp
    );

    // Upload a new file
    function uploadFile(
        string memory _ipfsHash,
        string memory _fileName,
        string memory _description,
        string memory _fileType,
        uint256 _fileSize,
        bool _isPublic
    ) public {
        require(bytes(_ipfsHash).length > 0, "IPFS hash cannot be empty");
        require(bytes(_fileName).length > 0, "File name cannot be empty");

        File memory newFile = File({
            ipfsHash: _ipfsHash,
            fileName: _fileName,
            timestamp: block.timestamp,
            owner: msg.sender,
            isPublic: _isPublic,
            description: _description,
            fileType: _fileType,
            fileSize: _fileSize,
            isDeleted: false
        });

        userFiles[msg.sender].push(newFile);

        emit FileUploaded(
            msg.sender,
            _ipfsHash,
            _fileName,
            block.timestamp,
            _isPublic
        );
    }

    // Delete a file
    function deleteFile(uint256 _fileIndex) public {
        require(_fileIndex < userFiles[msg.sender].length, "File index out of bounds");
        require(!userFiles[msg.sender][_fileIndex].isDeleted, "File already deleted");
        
        userFiles[msg.sender][_fileIndex].isDeleted = true;
        
        emit FileDeleted(
            msg.sender,
            _fileIndex,
            block.timestamp
        );
    }

    // Share a file with another user
    function shareFile(address _recipient, uint256 _fileIndex) public {
        require(_recipient != address(0), "Invalid recipient address");
        require(_recipient != msg.sender, "Cannot share with yourself");
        require(_fileIndex < userFiles[msg.sender].length, "File index out of bounds");
        require(!userFiles[msg.sender][_fileIndex].isDeleted, "Cannot share deleted file");

        SharedFile memory newSharedFile = SharedFile({
            fileIndex: _fileIndex,
            sharedBy: msg.sender,
            sharedAt: block.timestamp,
            hasAccess: true
        });

        sharedFiles[_recipient].push(newSharedFile);
        
        // Add recipient to fileRecipients if not already present
        bool recipientExists = false;
        for (uint i = 0; i < fileRecipients[msg.sender][_fileIndex].length; i++) {
            if (fileRecipients[msg.sender][_fileIndex][i] == _recipient) {
                recipientExists = true;
                break;
            }
        }
        if (!recipientExists) {
            fileRecipients[msg.sender][_fileIndex].push(_recipient);
        }
        recipientAccess[msg.sender][_fileIndex][_recipient] = true;

        emit FileShared(
            msg.sender,
            _recipient,
            _fileIndex,
            block.timestamp
        );
    }

    // Revoke access to a shared file
    function revokeAccess(address _recipient, uint256 _fileIndex) public {
        require(_recipient != address(0), "Invalid recipient address");
        require(_fileIndex < userFiles[msg.sender].length, "File index out of bounds");
        require(recipientAccess[msg.sender][_fileIndex][_recipient], "Recipient does not have access");
        
        recipientAccess[msg.sender][_fileIndex][_recipient] = false;
        
        // Update shared files access status
        SharedFile[] storage recipientFiles = sharedFiles[_recipient];
        for (uint256 i = 0; i < recipientFiles.length; i++) {
            if (recipientFiles[i].sharedBy == msg.sender && 
                recipientFiles[i].fileIndex == _fileIndex) {
                recipientFiles[i].hasAccess = false;
                break;
            }
        }
        
        emit FileAccessRevoked(
            msg.sender,
            _recipient,
            _fileIndex,
            block.timestamp
        );
    }

    // Grant access to a previously revoked file
    function grantAccess(address _recipient, uint256 _fileIndex) public {
        require(_recipient != address(0), "Invalid recipient address");
        require(_fileIndex < userFiles[msg.sender].length, "File index out of bounds");
        require(!userFiles[msg.sender][_fileIndex].isDeleted, "Cannot grant access to deleted file");
        
        recipientAccess[msg.sender][_fileIndex][_recipient] = true;
        
        // Update shared files access status
        SharedFile[] storage recipientFiles = sharedFiles[_recipient];
        for (uint256 i = 0; i < recipientFiles.length; i++) {
            if (recipientFiles[i].sharedBy == msg.sender && 
                recipientFiles[i].fileIndex == _fileIndex) {
                recipientFiles[i].hasAccess = true;
                break;
            }
        }
        
        emit FileAccessGranted(
            msg.sender,
            _recipient,
            _fileIndex,
            block.timestamp
        );
    }

    // Get all files for a specific user
    function getUserFiles(address _user) public view returns (File[] memory) {
        File[] memory allFiles = userFiles[_user];
        uint256 activeFileCount = 0;
        
        // Count non-deleted files
        for (uint256 i = 0; i < allFiles.length; i++) {
            if (!allFiles[i].isDeleted) {
                activeFileCount++;
            }
        }
        
        // Create array of active files
        File[] memory activeFiles = new File[](activeFileCount);
        uint256 currentIndex = 0;
        
        for (uint256 i = 0; i < allFiles.length; i++) {
            if (!allFiles[i].isDeleted) {
                activeFiles[currentIndex] = allFiles[i];
                currentIndex++;
            }
        }
        
        return activeFiles;
    }

    // Get all shared files for a user
    function getSharedFiles(address _user) public view returns (
        File[] memory files,
        address[] memory sharedBy,
        uint256[] memory sharedAt,
        bool[] memory hasAccess
    ) {
        SharedFile[] memory userSharedFiles = sharedFiles[_user];
        uint256 activeFileCount = 0;
        
        // Count active shared files
        for (uint256 i = 0; i < userSharedFiles.length; i++) {
            if (!userFiles[userSharedFiles[i].sharedBy][userSharedFiles[i].fileIndex].isDeleted) {
                activeFileCount++;
            }
        }
        
        files = new File[](activeFileCount);
        sharedBy = new address[](activeFileCount);
        sharedAt = new uint256[](activeFileCount);
        hasAccess = new bool[](activeFileCount);
        
        uint256 currentIndex = 0;
        
        for (uint256 i = 0; i < userSharedFiles.length; i++) {
            SharedFile memory sharedFile = userSharedFiles[i];
            File memory originalFile = userFiles[sharedFile.sharedBy][sharedFile.fileIndex];
            
            if (!originalFile.isDeleted) {
                files[currentIndex] = originalFile;
                sharedBy[currentIndex] = sharedFile.sharedBy;
                sharedAt[currentIndex] = sharedFile.sharedAt;
                hasAccess[currentIndex] = sharedFile.hasAccess;
                currentIndex++;
            }
        }

        return (files, sharedBy, sharedAt, hasAccess);
    }

    // Get file details
    function getFileDetails(address _user, uint256 _fileIndex) 
        public 
        view 
        returns (
            string memory ipfsHash,
            string memory fileName,
            uint256 timestamp,
            bool isPublic,
            string memory description,
            string memory fileType,
            uint256 fileSize
        ) 
    {
        require(_fileIndex < userFiles[_user].length, "File index out of bounds");
        File memory file = userFiles[_user][_fileIndex];
        require(!file.isDeleted, "File has been deleted");
        
        return (
            file.ipfsHash,
            file.fileName,
            file.timestamp,
            file.isPublic,
            file.description,
            file.fileType,
            file.fileSize
        );
    }

    // Toggle file visibility
    function toggleFileVisibility(uint256 _fileIndex) public {
        require(_fileIndex < userFiles[msg.sender].length, "File index out of bounds");
        require(!userFiles[msg.sender][_fileIndex].isDeleted, "Cannot modify deleted file");
        userFiles[msg.sender][_fileIndex].isPublic = !userFiles[msg.sender][_fileIndex].isPublic;
    }

    // Get shared file recipients
    function getSharedFileRecipients(uint256 _fileIndex) public view returns (
        address[] memory recipients,
        bool[] memory accessStatus
    ) {
        require(_fileIndex < userFiles[msg.sender].length, "File index out of bounds");
        require(!userFiles[msg.sender][_fileIndex].isDeleted, "File has been deleted");
        
        address[] memory allRecipients = fileRecipients[msg.sender][_fileIndex];
        recipients = new address[](allRecipients.length);
        accessStatus = new bool[](allRecipients.length);
        
        for (uint256 i = 0; i < allRecipients.length; i++) {
            recipients[i] = allRecipients[i];
            accessStatus[i] = recipientAccess[msg.sender][_fileIndex][allRecipients[i]];
        }
        
        return (recipients, accessStatus);
    }
}