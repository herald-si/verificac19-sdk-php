<?php

namespace Herald\GreenPass;

class GPDataTest
{
    private const GP_CERTIFICATORE = 'Ministero della Salute';

    private const GP_VERSION = '1.0.0';

    private const GP_TG = '840539006';

    public static $qrcode_certificate_valid_but_revoked = 'HC1:6BFOXN%TSMAHN-H3YS1IK47ES6IXJR4E47X5*T917VF+UOGIS1RYZV:X9:IMJZTCV4*XUA2PSGH.+H$NI4L6HUC%UG/YL WO*Z7ON13:LHNG7H8H%BFP8FG4T 9OKGUXI$NIUZUK*RIMI4UUIMI.J9WVHWVH+ZEOV1AT1HRI2UHD4TR/S09T./08H0AT1EYHEQMIE9WT0K3M9UVZSVV*001HW%8UE9.955B9-NT0 2$$0X4PCY0+-CVYCRMTB*05*9O%0HJP7NVDEBO584DKH78$ZJ*DJWP42W5P0QMO6C8PL353X7H1RU0P48PCA7T5MCH5:ZJ::AKU2UM97H98$QP3R8BH9LV3*O-+DV8QJHHY4I4GWU-LU7T9.V+ T%UNUWUG+M.1KG%VWE94%ALU47$71MFZJU*HFW.6$X50*MSYOJT1MR96/1Z%FV3O-0RW/Q.GMCQS%NE';

    public static $qrcode_without_hc1 = '6BFOXN%TSMAHN-H3YS1IK47ES6IXJR4E47X5*T917VF+UOGIS1RYZV:X9:IMJZTCV4*XUA2PSGH.+H$NI4L6HUC%UG/YL WO*Z7ON13:LHNG7H8H%BFP8FG4T 9OKGUXI$NIUZUK*RIMI4UUIMI.J9WVHWVH+ZEOV1AT1HRI2UHD4TR/S09T./08H0AT1EYHEQMIE9WT0K3M9UVZSVV*001HW%8UE9.955B9-NT0 2$$0X4PCY0+-CVYCRMTB*05*9O%0HJP7NVDEBO584DKH78$ZJ*DJWP42W5P0QMO6C8PL353X7H1RU0P48PCA7T5MCH5:ZJ::AKU2UM97H98$QP3R8BH9LV3*O-+DV8QJHHY4I4GWU-LU7T9.V+ T%UNUWUG+M.1KG%VWE94%ALU47$71MFZJU*HFW.6$X50*MSYOJT1MR96/1Z%FV3O-0RW/Q.GMCQS%NE';

    public static $qrcode_new_zeland_gp = 'NZCP:/1/2KCEVIQEIVVWK6JNGEASNICZAEP2KALYDZSGSZB2O5SWEOTOPJRXALTDN53GSZBRHEXGQZLBNR2GQLTOPICRUYMBTIFAIGTUKBAAUYTWMOSGQQDDN5XHIZLYOSBHQJTIOR2HA4Z2F4XXO53XFZ3TGLTPOJTS6MRQGE4C6Y3SMVSGK3TUNFQWY4ZPOYYXQKTIOR2HA4Z2F4XW46TDOAXGG33WNFSDCOJONBSWC3DUNAXG46RPMNXW45DFPB2HGL3WGFTXMZLSONUW63TFGEXDALRQMR2HS4DFQJ2FMZLSNFTGSYLCNRSUG4TFMRSW45DJMFWG6UDVMJWGSY2DN53GSZCQMFZXG4LDOJSWIZLOORUWC3CTOVRGUZLDOSRWSZ3JOZSW4TTBNVSWISTBMNVWUZTBNVUWY6KOMFWWKZ2TOBQXE4TPO5RWI33CNIYTSNRQFUYDILJRGYDVAYFE6VGU4MCDGK7DHLLYWHVPUS2YIDJOA6Y524TD3AZRM263WTY2BE4DPKIF27WKF3UDNNVSVWRDYIYVJ65IRJJJ6Z25M2DO4YZLBHWFQGVQR5ZLIWEQJOZTS3IQ7JTNCFDX';

    public static $qrcode_de_test_kid_invalid = 'HC1:NCFOXNEG2NBJ5*H:QO-.O9B3QZ8Y*M9WL7LG4/8+W4VGAXOE4+4J59BZ6%-OD 4YQFPF6R:5SVBWVBDKBYLDR4DF4D$ZJ*DJWP42W5J3U4OG7.R7%NC.UPTUD*Q9RK7RMEN4CD1B+K8AV2PTO*N--T0SFXZQ H9RQGX-FO2WYZQ2J95J02O8..V$T7%$D4J8$T7T$7YNGHM4PRAAUICO1DV59UE6Q1M650 LHZA0D9E2LBHHGKLO-K%FGLIA5D8MJKQJK JMDJL9GG.IA.C8KRDL4O54O4IGUJKJGI.IAHLCV5GVWNZIKXGG JMLII7EDTG91PC3DE0OARH9W/IO6AHCRTWA4EQN95N14Z+HP+POC1.AO5PIZ.VTZOSV0I+QWZJHN1ZBQR*MTNK EM5MGPI5A-M8F7AJOZNV9JHKIJYE9*FJ+UVAZ8-.A2*CEHJ5$0O/A%4SL/IG%8R.9Z6TG0MW%8N*48-930J7*4E%2L+9N2LY2Q%%2G0M172ZUJYBW897MJM5DB0J4XETW8PX+KN4K.-V:3WROR$04.7E93Q6VUE$TO%R$:3HUCQZ6D8OG2B:%A6-I8PJ8%VKYOU1Q96E01MRKUU2G730F%2H2';

    public static $qrcode_ch = 'HC1:NCFK60DG0/3WUWGSLKH47GO0:S4KQDITFAUO9CK-500XK0JCV496F3JBS33S3F3MU394SY50.FK6ZK7:EDOLOPCO8F6%E3.DA%EOPC1G72A6YM86G77460A6TL6IL6G*8J*8:Q6E46VM8K:6 47FN8UPC0JCZ69FVCPD0LVC6JD846Y96E463W5.A6+EDG8F3I80/D6$CBECSUER:C2$NS346$C2%E9VC- CSUE145GB8JA5B$D% D3IA4W5646646-96:96.JCP9EJY8L/5M/5546.96SF63KC.SC4KCD3DX47B46IL6646H*6Z/ER2DD46JH8946JPCT3E5JDLA7$Q69464W51S6..DX%DZJC2/DYOA$$E5$C JC3/D9Z95LEZED1ECW.C8WE2OA3ZAGY8MPCG/DU2DRB8MTA8+9$PC5$CUZC$$5Y$5FBBC30.9V$*J7TUO*9T$MJ5FT9U:HMD$EUJD:IG/64QL452M4KJH8S7$9N:655W*:OY6M+M3GH0N4F9YN-W91UHUO8BBJH64H8N/5LN%05P0-KG87ONE58+M%N8FJJT9S+K65WRNN2D AM/H*UQ6SEBG71Q1R5H68KTL2R*FDP5DBGWEM18J45MS$5O.296HOMB9 CTTOZN8:MB95ITP045OUQ41AAB1K19W5L84%G%9H5Z57LRP$IH$K0PLLFCCNE8QG%8LRWN$1P$IA+3I:.3$JB:DUK%DQYIO5GRJF*$G*EMHV3RMLHISWW8FJIHGQ*:BV7N+CA:VVXOILXHDUJQL9LTNQ1THAB$EAGYU07V 89NNLA$NS7F8ENV:COAAJ+F-2NK+P-3';

    public static $vaccine = [
        'v' => [
            '0' => [
                'dn' => '2',
                'ma' => 'ORG-100031184',
                'vp' => '1119349007',
                'dt' => '2021-08-13',
                'co' => 'IT',
                'ci' => 'FAKEID#0',
                'mp' => 'EU/1/20/1507',
                'is' => self::GP_CERTIFICATORE,
                'sd' => '2',
                'tg' => self::GP_TG,
            ],
        ],
        'nam' => [
            'fnt' => 'UTENTE',
            'fn' => 'UTENTE',
            'gnt' => 'TEST',
            'gn' => 'TEST',
        ],
        'ver' => self::GP_VERSION,
        'dob' => '1999-12-12',
    ];

    public static $testresult = [
        't' => [
            '0' => [
                'sc' => '2021-10-13T18:45:00+02:00',
                'ma' => '1324',
                'tt' => 'LP217198-3',
                'co' => 'IT',
                'tc' => 'PROVA SNC',
                'ci' => 'TESTIDFAKE#2',
                'is' => self::GP_CERTIFICATORE,
                'tg' => self::GP_TG,
                'tr' => '260415000',
            ],
        ],
        'nam' => [
            'fnt' => 'UTENTE',
            'fn' => 'UTENTE',
            'gnt' => 'TEST',
            'gn' => 'TEST',
        ],
        'ver' => self::GP_VERSION,
        'dob' => '1989-11-15',
    ];

    public static $recovery = [
        'r' => [
            '0' => [
                'fr' => '2021-01-13',
                'df' => '2021-01-13',
                'du' => '2021-12-13',
                'co' => 'IT',
                'ci' => 'TESTIDFAKERECOVERY#2',
                'is' => self::GP_CERTIFICATORE,
                'tg' => self::GP_TG,
            ],
        ],
        'nam' => [
            'fnt' => 'UTENTE',
            'fn' => 'UTENTE',
            'gnt' => 'TEST',
            'gn' => 'TEST',
        ],
        'ver' => self::GP_VERSION,
        'dob' => '1955-01-20',
    ];

    public static $exemption = [
        'e' => [
            '0' => [
                'df' => '2021-02-15',
                'du' => '2021-12-15',
                'co' => 'IT',
                'ci' => 'TESTIDFAKEEXEMPTION#2',
                'is' => self::GP_CERTIFICATORE,
                'tg' => self::GP_TG,
            ],
        ],
        'nam' => [
            'fnt' => 'UTENTE',
            'fn' => 'UTENTE',
            'gnt' => 'TEST',
            'gn' => 'TEST',
        ],
        'ver' => self::GP_VERSION,
        'dob' => '1955-01-20',
    ];
}
