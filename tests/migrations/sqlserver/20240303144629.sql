-- Create "users" table
CREATE TABLE [users] (
  [id] int IDENTITY (1, 1) NOT NULL,
  [name] nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
  CONSTRAINT [PK_users] PRIMARY KEY CLUSTERED ([id] ASC)
);
-- Create "bugs" table
CREATE TABLE [bugs] (
  [id] int IDENTITY (1, 1) NOT NULL,
  [description] nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
  [created] datetime2(6) NOT NULL,
  [status] nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
  [engineer_id] int NULL,
  [reporter_id] int NULL,
  CONSTRAINT [PK_bugs] PRIMARY KEY CLUSTERED ([id] ASC),
 
  CONSTRAINT [FK_1E197C9E1CFE6F5] FOREIGN KEY ([reporter_id]) REFERENCES [users] ([id]) ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT [FK_1E197C9F8D8CDF1] FOREIGN KEY ([engineer_id]) REFERENCES [users] ([id]) ON UPDATE NO ACTION ON DELETE NO ACTION
);
-- Create index "IDX_1E197C9F8D8CDF1" to table: "bugs"
CREATE NONCLUSTERED INDEX [IDX_1E197C9F8D8CDF1] ON [bugs] ([engineer_id] ASC);
-- Create index "IDX_1E197C9E1CFE6F5" to table: "bugs"
CREATE NONCLUSTERED INDEX [IDX_1E197C9E1CFE6F5] ON [bugs] ([reporter_id] ASC);
