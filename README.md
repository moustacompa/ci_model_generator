# ci_model_generator
This is a model generator for Codeigniter 3
This code is the version 2.2 of the tool

MGenerator is a code model generation tool that works with Codeigniter 3.
It automatically generates the model code corresponding to your database. The code consists of CIModels classes, compatible with Codeigniter.
How to use this code? This is done in 8 steps:
1- Download the files and place the mgenerator-latest-xx.php file in the application folder of your project;
2- Change the connection parameters to your database;
3- Check if the address of the folder containing php.exe (or php under linux) is added to the PATH environment variable, if not, add it;
4- Open your terminal / command prompt and go to the application folder of your project;
5- Execute the command ./php mgenerator-latest-xx.php (where xx represents the version number of the tool). If there are database connection problems or any other error, it will be displayed and you can correct it. Otherwise, the command will display the list of generated classes with the progress of the generation. Once the generation is complete, you can find the classes in the Models folder of your project;
6- Add the code below in the autoload.php file located in the config folder:
$ dir = './application/models';
$ files = scandir ($ dir);
$ models = array ();
foreach ($ files as $ f) {
    $ file_parts = pathinfo ($ f);
    $ file_parts ['extension'];
    $ correct_extension = Array ('php');
    if (in_array ($ file_parts ['extension'], $ correct_extension)) {
        array_push ($ models, str_replace ('. php', '', $ f));
    }
}
$ autoload ['model'] = $ models;
This will allow Codeigniter to recognize the classes thus generated.
7- Copy the Model.php file into the models folder of your project;
8- If you modify the structure of your database, you must redo steps 4 and 5 to update the model.

!!!Novelty
Version 2.2 allows you to specify the classes to generate, in case your database contains data from several applications.
To do this, specify the names of the tables to be taken into account for the generation of the model on line 20. Otherwise, comment out line 20 and uncomment line 21.
Thank you and feel free to send me your comments on using the tool 
!!!!!Thank you!!!!!
