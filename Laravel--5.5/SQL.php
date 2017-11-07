<?php

$sql = "
            SELECT
            a.ss_id,a.sub_uid,a.sub_mobile,
            b.shop_id,b.title,
            sum(CASE c.pei_type
            WHEN 1 THEN
            (
                (
                    c.amount + c.money + c.first_youhui - c.pei_amount
                ) * ((100 - b.service_charge) / 100)
            )
            ELSE
            (
                (
                    c.amount + c.money + c.first_youhui
                ) * ((100 - b.service_charge) / 100)
            )
            END) AS shopIncome,
            sum(CASE c.pei_type
            WHEN 1 THEN
            (
                (
                    c.amount + c.money + c.first_youhui - c.pei_amount
                ) * ((100 - b.service_charge) / 100)
            )
            ELSE
            (
                (
                    c.amount + c.money + c.first_youhui
                ) * ((100 - b.service_charge) / 100)
            )
            END) / 100 AS myIncome,
            FROM_UNIXTIME(c.dateline, '%Y-%m-%d') AS PayTime
            FROM
            shareshop AS a
            LEFT JOIN jh_shop AS b ON a.sub_mobile = b.mobile
            LEFT JOIN jh_order AS c ON b.shop_id = c.shop_id
            WHERE
            a.ss_id=$ss_id
            AND c.pay_status = 1
            AND c.order_status = 8
            AND c.dateline>=$firstDay
            AND c.dateline<=$lastDay
            GROUP BY c.day
            ORDER BY
            c.day DESC
            LIMIT $start,$scale
        ";

$sql = "
		SELECT
			a.shop_id,
			b.order_id,
			a.title AS 店铺名,
			CASE b.pei_type
		WHEN 1 THEN
			(
				(
					b.amount + b.money + b.first_youhui - b.pei_amount
				) * ((100 - a.service_charge) / 100)
			)
		ELSE
			(
				(
					b.amount + b.money + b.first_youhui
				) * ((100 - a.service_charge) / 100)
			)
		END AS 商家应得,
		 FROM_UNIXTIME(pay.payedtime) AS 支付时间
		FROM
			jh_shop AS a
		LEFT JOIN jh_order AS b ON a.shop_id = b.shop_id
		LEFT JOIN jh_payment_log pay ON pay.order_id = b.order_id
		WHERE
		a.shop_id=64
		AND b.pay_status = 1
		AND b.order_status = 8
		AND pay.payed = 1
		GROUP BY pay.payedtime
		ORDER BY
			a.shop_id,
			b.pay_time DESC
		limit 10
	";
