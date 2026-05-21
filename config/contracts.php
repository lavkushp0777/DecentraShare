<?php

return [
    'contract' => [
        'address' => getenv('CONTRACT_ADDRESS') ?: '0x70dD105c6D5F4be9aa803618abfCbBC5Fa1B1B82',
        'abi' => [
            [
                "inputs" => [
                    ["internalType" => "string", "name" => "_ipfsHash", "type" => "string"],
                    ["internalType" => "string", "name" => "_fileName", "type" => "string"],
                    ["internalType" => "string", "name" => "_description", "type" => "string"],
                    ["internalType" => "string", "name" => "_fileType", "type" => "string"],
                    ["internalType" => "uint256", "name" => "_fileSize", "type" => "uint256"],
                    ["internalType" => "bool", "name" => "_isPublic", "type" => "bool"]
                ],
                "name" => "uploadFile",
                "outputs" => [],
                "stateMutability" => "nonpayable",
                "type" => "function"
            ],
            [
                "inputs" => [["internalType" => "uint256", "name" => "_fileIndex", "type" => "uint256"]],
                "name" => "deleteFile",
                "outputs" => [],
                "stateMutability" => "nonpayable",
                "type" => "function"
            ],
            [
                "inputs" => [
                    ["internalType" => "address", "name" => "_recipient", "type" => "address"],
                    ["internalType" => "uint256", "name" => "_fileIndex", "type" => "uint256"]
                ],
                "name" => "shareFile",
                "outputs" => [],
                "stateMutability" => "nonpayable",
                "type" => "function"
            ],
            [
                "inputs" => [
                    ["internalType" => "address", "name" => "_recipient", "type" => "address"],
                    ["internalType" => "uint256", "name" => "_fileIndex", "type" => "uint256"]
                ],
                "name" => "revokeAccess",
                "outputs" => [],
                "stateMutability" => "nonpayable",
                "type" => "function"
            ],
            [
                "inputs" => [
                    ["internalType" => "address", "name" => "_recipient", "type" => "address"],
                    ["internalType" => "uint256", "name" => "_fileIndex", "type" => "uint256"]
                ],
                "name" => "grantAccess",
                "outputs" => [],
                "stateMutability" => "nonpayable",
                "type" => "function"
            ],
            [
                "inputs" => [["internalType" => "uint256", "name" => "_fileIndex", "type" => "uint256"]],
                "name" => "getSharedFileRecipients",
                "outputs" => [
                    ["internalType" => "address[]", "name" => "recipients", "type" => "address[]"],
                    ["internalType" => "bool[]", "name" => "accessStatus", "type" => "bool[]"]
                ],
                "stateMutability" => "view",
                "type" => "function"
            ],
            [
                "inputs" => [["internalType" => "address", "name" => "_user", "type" => "address"]],
                "name" => "getUserFiles",
                "outputs" => [
                    [
                        "components" => [
                            ["internalType" => "string", "name" => "ipfsHash", "type" => "string"],
                            ["internalType" => "string", "name" => "fileName", "type" => "string"],
                            ["internalType" => "uint256", "name" => "timestamp", "type" => "uint256"],
                            ["internalType" => "address", "name" => "owner", "type" => "address"],
                            ["internalType" => "bool", "name" => "isPublic", "type" => "bool"],
                            ["internalType" => "string", "name" => "description", "type" => "string"],
                            ["internalType" => "string", "name" => "fileType", "type" => "string"],
                            ["internalType" => "uint256", "name" => "fileSize", "type" => "uint256"]
                        ],
                        "internalType" => "struct FileSharing.File[]",
                        "name" => "",
                        "type" => "tuple[]"
                    ]
                ],
                "stateMutability" => "view",
                "type" => "function"
            ],
            [
                "inputs" => [["internalType" => "address", "name" => "_user", "type" => "address"]],
                "name" => "getSharedFiles",
                "outputs" => [
                    [
                        "components" => [
                            ["internalType" => "string", "name" => "ipfsHash", "type" => "string"],
                            ["internalType" => "string", "name" => "fileName", "type" => "string"],
                            ["internalType" => "uint256", "name" => "timestamp", "type" => "uint256"],
                            ["internalType" => "address", "name" => "owner", "type" => "address"],
                            ["internalType" => "bool", "name" => "isPublic", "type" => "bool"],
                            ["internalType" => "string", "name" => "description", "type" => "string"],
                            ["internalType" => "string", "name" => "fileType", "type" => "string"],
                            ["internalType" => "uint256", "name" => "fileSize", "type" => "uint256"]
                        ],
                        "internalType" => "struct FileSharing.File[]",
                        "name" => "files",
                        "type" => "tuple[]"
                    ],
                    ["internalType" => "address[]", "name" => "sharedBy", "type" => "address[]"],
                    ["internalType" => "uint256[]", "name" => "sharedAt", "type" => "uint256[]"],
                    ["internalType" => "bool[]", "name" => "hasAccess", "type" => "bool[]"]
                ],
                "stateMutability" => "view",
                "type" => "function"
            ],
            [
                "inputs" => [["internalType" => "uint256", "name" => "_fileIndex", "type" => "uint256"]],
                "name" => "toggleFileVisibility",
                "outputs" => [],
                "stateMutability" => "nonpayable",
                "type" => "function"
            ]
        ]
    ],
    'ipfs' => [
        'provider' => getenv('IPFS_PROVIDER') ?: 'pinata',
        'pinata' => [
            'api_key' => getenv('PINATA_API_KEY'),
            'secret_key' => getenv('PINATA_SECRET_KEY'),
            'api_url' => 'https://api.pinata.cloud/pinning/pinFileToIPFS'
        ],
        'web3storage' => [
            'token' => getenv('WEB3_STORAGE_TOKEN'),
            'api_url' => 'https://api.web3.storage/upload'
        ],
        'custom' => [
            'api_url' => getenv('IPFS_API_URL') ?: 'https://api.ipfs.io',
            'gateway_url' => getenv('IPFS_GATEWAY_URL') ?: 'https://gateway.pinata.cloud/ipfs/'
        ]
    ],
    'web3' => [
        'rpc_url' => getenv('WEB3_RPC_URL') ?: 'https://eth-mainnet.alchemyapi.io/v2/demo',
        'chain_id' => 1
    ]
];
