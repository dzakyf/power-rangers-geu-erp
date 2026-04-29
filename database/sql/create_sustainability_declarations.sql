CREATE TABLE `sustainability_declarations` (
    `ID`                        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `SD_No`                     VARCHAR(50)     NOT NULL,
    `Issue_Date`                DATE            NULL,

    -- Supplier / Recipient
    `Consignee`                 VARCHAR(100)    NULL,
    `Certificate_No`            VARCHAR(100)    NULL,
    `Contract_No`               VARCHAR(100)    NULL,

    -- Dispatch & Receipt address
    `Dispatch_Address`          TEXT            NULL,
    `Dispatch_Same_As_Supplier` TINYINT(1)      NOT NULL DEFAULT 0,
    `Receipt_Address`           TEXT            NULL,
    `Receipt_Same_As_Recipient` TINYINT(1)      NOT NULL DEFAULT 0,
    `Dispatch_Date`             DATE            NULL,

    -- Section 1: General Information
    `Product_Type`              VARCHAR(100)    NOT NULL DEFAULT 'Raw Material',
    `Raw_Material_Type`         VARCHAR(255)    NOT NULL DEFAULT 'Used cooking oil (UCO) entirely of veg. origin',
    `Additional_Info`           TEXT            NULL,
    `Country_Of_Origin`         VARCHAR(100)    NOT NULL DEFAULT 'Indonesia',
    `Quantity`                  DECIMAL(15,3)   NOT NULL DEFAULT 0,
    `Quantity_Unit`             VARCHAR(20)     NOT NULL DEFAULT 'metric tons',
    `ISCC_Compliant`            TINYINT(1)      NOT NULL DEFAULT 1,
    `EU_RED_Compliant`          TINYINT(1)      NOT NULL DEFAULT 1,
    `Chain_Of_Custody`          VARCHAR(100)    NOT NULL DEFAULT 'Mass balance',

    -- Section 2: Scope of Certification
    `Scope_Art29`               VARCHAR(3)      NOT NULL DEFAULT 'Yes',
    `Scope_Intermediate_Crop`   VARCHAR(3)      NOT NULL DEFAULT 'Yes',
    `Scope_Low_ILUC`            VARCHAR(3)      NOT NULL DEFAULT 'Yes',
    `Scope_Waste_Residue`       VARCHAR(3)      NOT NULL DEFAULT 'Yes',
    `Waste_Permit_No`           VARCHAR(200)    NULL,
    `Scope_Support_Received`    VARCHAR(3)      NOT NULL DEFAULT 'No',
    `Support_Nature`            TEXT            NULL,

    -- Section 3: GHG Emission Information
    `GHG_Total_Default`         VARCHAR(3)      NOT NULL DEFAULT 'No',
    `GHG_Eec`                   VARCHAR(50)     NULL,
    `GHG_El`                    VARCHAR(50)     NULL,
    `GHG_Ep`                    VARCHAR(50)     NULL,
    `GHG_Etd`                   VARCHAR(50)     NULL DEFAULT 'DDV',
    `GHG_Eu`                    VARCHAR(50)     NULL,
    `GHG_Esca`                  VARCHAR(50)     NULL,
    `GHG_Eccs`                  VARCHAR(50)     NULL,
    `GHG_Eccr`                  VARCHAR(50)     NULL,
    `Transport_Ship_KM`         DECIMAL(10,2)   NULL,
    `Transport_Train_KM`        DECIMAL(10,2)   NULL,
    `Transport_Road_KM`         DECIMAL(10,2)   NULL DEFAULT 3.30,
    `GHG_DDV_Oil_Extraction`    TINYINT(1)      NOT NULL DEFAULT 1,
    `GHG_DDV_Soil_N2O`          TINYINT(1)      NOT NULL DEFAULT 1,
    `GHG_Esca_Bonus_Cap`        TEXT            NULL,
    `GHG_NUTS2_Area`            VARCHAR(100)    NULL,
    `GHG_El_Bonus`              VARCHAR(3)      NOT NULL DEFAULT 'Yes',

    -- Section 4: Voluntary Add-Ons
    `Addon_FSS`                 TINYINT(1)      NOT NULL DEFAULT 1,
    `Addon_EUDR`                TINYINT(1)      NOT NULL DEFAULT 1,
    `Addon_NonGMO_Feed`         TINYINT(1)      NOT NULL DEFAULT 1,
    `Addon_NonGMO_Technical`    TINYINT(1)      NOT NULL DEFAULT 1,

    `Note`                      TEXT            NULL,
    `Status`                    VARCHAR(50)     NOT NULL DEFAULT 'CREATED',
    `Created_By`                VARCHAR(100)    NULL,
    `Created_Date`              DATETIME        NULL,

    PRIMARY KEY (`ID`),
    UNIQUE KEY `uniq_sd_no` (`SD_No`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
