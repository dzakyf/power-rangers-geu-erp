CREATE TABLE `delivery_notes` (
    `ID`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `DN_No`             VARCHAR(100)    NOT NULL,
    `Consignee`         VARCHAR(100)    NULL,
    `Supplier_ISCC_Ref` VARCHAR(200)    NULL,
    `Contract_No`       VARCHAR(100)    NULL,
    `Product`           VARCHAR(255)    NOT NULL DEFAULT 'Used Cooking Oil (UCO) entirely of Vege-Oil Origin',
    `Total_Tonnage`     DECIMAL(15,3)   NOT NULL DEFAULT 0,
    `Delivery_Date`     DATE            NULL,
    `Country_Of_Origin` VARCHAR(100)    NOT NULL DEFAULT 'Indonesia',
    `GHG_Value`         TEXT            NULL,
    `Note`              TEXT            NULL,
    `Status`            VARCHAR(50)     NOT NULL DEFAULT 'CREATED',
    `Created_By`        VARCHAR(100)    NULL,
    `Created_Date`      DATETIME        NULL,
    PRIMARY KEY (`ID`),
    UNIQUE KEY `uniq_dn_no` (`DN_No`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
