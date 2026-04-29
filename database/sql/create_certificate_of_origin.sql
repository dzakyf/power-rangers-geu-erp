CREATE TABLE `certificate_of_origin` (
    `ID`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `COO_No`            VARCHAR(100)    NOT NULL,
    `Consignee`         VARCHAR(100)    NULL,
    `Fin_Inv_No`        VARCHAR(100)    NULL,
    `Fin_Inv_Date`      DATE            NULL,
    `Contract_No`       VARCHAR(100)    NULL,
    `Method_Of_Dispatch` VARCHAR(100)   NOT NULL DEFAULT 'Sea',
    `Shipment_Type`     VARCHAR(100)    NOT NULL DEFAULT 'CIF',
    `Vessel`            VARCHAR(200)    NULL,
    `Voyage_No`         VARCHAR(100)    NULL,
    `Port_Of_Loading`   VARCHAR(200)    NULL,
    `Date_Of_Departure` DATE            NULL,
    `Port_Of_Discharge` VARCHAR(200)    NULL,
    `Final_Destination` VARCHAR(200)    NULL,
    `Kind_Packages`     VARCHAR(200)    NULL,
    `Good_Description`  VARCHAR(255)    NOT NULL DEFAULT 'REFINED USED COOKING OIL',
    `HS_Code`           VARCHAR(50)     NOT NULL DEFAULT '15119020',
    `Net_Weight`        DECIMAL(15,3)   NOT NULL DEFAULT 0,
    `Place_Of_Issue`    VARCHAR(100)    NOT NULL DEFAULT 'Surabaya',
    `Issue_Date`        DATE            NULL,
    `Note`              TEXT            NULL,
    `Status`            VARCHAR(50)     NOT NULL DEFAULT 'CREATED',
    `Created_By`        VARCHAR(100)    NULL,
    `Created_Date`      DATETIME        NULL,
    PRIMARY KEY (`ID`),
    UNIQUE KEY `uniq_coo_no` (`COO_No`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `certificate_of_origin_dtl` (
    `ID`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `COO_No`        VARCHAR(100)    NOT NULL,
    `Container_No`  VARCHAR(100)    NOT NULL,
    `Seal_No`       VARCHAR(100)    NULL,
    PRIMARY KEY (`ID`),
    KEY `idx_coo_no` (`COO_No`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
