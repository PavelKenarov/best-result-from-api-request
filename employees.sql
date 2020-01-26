--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` bigint(12) UNSIGNED NOT NULL,
  `uuid` varchar(155) NOT NULL,
  `company` varchar(255) NOT NULL,
  `status` enum('finished','avatar','badhost','inactive') NOT NULL DEFAULT 'avatar',
  `bio` varchar(500) NOT NULL,
  `name` varchar(255) NOT NULL,
  `title` varchar(155) NOT NULL,
  `avatar` varchar(255) NOT NULL,
  `ts` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`);

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(12) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
COMMIT;
